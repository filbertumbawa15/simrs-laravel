<?php

namespace App\Services;

use App\Enums\MetodePembayaran;
use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\StatusTagihan;
use App\Models\Kunjungan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate tagihan dari semua item yang sudah tercatat di kunjungan:
     * - Konsultasi (dari jasa_konsul dokter RJ)
     * - Tindakan (dari tindakan_kunjungan)
     * - Lab (dari order_lab_detail)
     * - Obat (dari resep yang sudah diserahkan)
     * - Kamar (dari kamar_inap untuk RI)
     *
     * Idempotent: kalau tagihan sudah ada, kembalikan yang existing.
     */
    public function generateTagihan(Kunjungan $kunjungan): Tagihan
    {
        return DB::transaction(function () use ($kunjungan) {
            $tagihan = Tagihan::firstOrCreate(
                ['kunjungan_id' => $kunjungan->id],
                [
                    'no_tagihan' => Tagihan::generateNoTagihan(),
                    'tgl_tagihan' => now(),
                    'status' => StatusTagihan::Draft,
                ]
            );

            // Refresh semua detail
            $tagihan->details()->delete();

            $kunjungan->load([
                'rawatJalan.dokter',
                'rawatInap.kamarInap.kamar.kelas',
                'tindakan.tindakan',
                'orderLab.details.parameter',
                'resep.details',
            ]);

            // 1. Konsultasi (untuk RJ)
            if ($kunjungan->rawatJalan && $kunjungan->rawatJalan->dokter) {
                $dokter = $kunjungan->rawatJalan->dokter;
                if ($dokter->jasa_konsul > 0) {
                    $this->tambahDetail($tagihan, [
                        'kategori' => 'KONSULTASI',
                        'deskripsi' => 'Konsultasi ' . $dokter->nama_lengkap,
                        'qty' => 1,
                        'harga' => $dokter->jasa_konsul,
                        'subtotal' => $dokter->jasa_konsul,
                    ]);
                }
            }

            // 2. Tindakan medis
            foreach ($kunjungan->tindakan as $tk) {
                $this->tambahDetail($tagihan, [
                    'kategori' => 'TINDAKAN',
                    'referensi_type' => \App\Models\TindakanKunjungan::class,
                    'referensi_id' => $tk->id,
                    'deskripsi' => $tk->tindakan->nama,
                    'qty' => $tk->qty,
                    'harga' => (float) $tk->tarif,
                    'subtotal' => (float) $tk->subtotal,
                ]);
            }

            // 3. Lab — group per order
            foreach ($kunjungan->orderLab as $order) {
                foreach ($order->details as $detailOrder) {
                    $this->tambahDetail($tagihan, [
                        'kategori' => 'LAB',
                        'referensi_type' => \App\Models\OrderLab::class,
                        'referensi_id' => $order->id,
                        'deskripsi' => 'Lab: ' . $detailOrder->parameter->nama,
                        'qty' => 1,
                        'harga' => (float) $detailOrder->tarif,
                        'subtotal' => (float) $detailOrder->tarif,
                    ]);
                }
            }

            // 4. Obat — hanya resep yang sudah DISERAHKAN
            foreach ($kunjungan->resep->where('status', 'DISERAHKAN') as $resep) {
                foreach ($resep->details as $detailResep) {
                    $this->tambahDetail($tagihan, [
                        'kategori' => 'FARMASI',
                        'referensi_type' => \App\Models\Resep::class,
                        'referensi_id' => $resep->id,
                        'deskripsi' => $detailResep->obat->nama . ' (' . $detailResep->signa . ')',
                        'qty' => $detailResep->jumlah,
                        'harga' => (float) $detailResep->harga_satuan,
                        'subtotal' => (float) $detailResep->subtotal,
                    ]);
                }
            }

            // 5. Kamar (untuk RI)
            if ($kunjungan->rawatInap) {
                foreach ($kunjungan->rawatInap->kamarInap as $ki) {
                    $hari = $ki->durasi_hari;
                    $tarif = (float) $ki->kamar->kelas->tarif_per_hari;
                    $this->tambahDetail($tagihan, [
                        'kategori' => 'KAMAR',
                        'referensi_type' => \App\Models\KamarInap::class,
                        'referensi_id' => $ki->id,
                        'deskripsi' => "Kamar {$ki->kamar->no_kamar} (Kelas {$ki->kamar->kelas->nama})",
                        'qty' => $hari,
                        'harga' => $tarif,
                        'subtotal' => $tarif * $hari,
                    ]);
                }
            }

            // Hitung ulang totals
            $this->hitungUlangTotal($tagihan);

            return $tagihan->fresh('details');
        });
    }

    /**
     * Finalize tagihan — kunci agar tidak bisa diubah lagi.
     */
    public function finalize(Tagihan $tagihan, string $userId): Tagihan
    {
        if ($tagihan->status !== StatusTagihan::Draft) {
            throw new \DomainException('Hanya tagihan draft yang bisa difinalisasi.');
        }

        $this->hitungUlangTotal($tagihan);

        $kunjungan = $tagihan->kunjungan;
        $statusBaru = $kunjungan->penjamin === Penjamin::Umum
            ? StatusTagihan::BelumLunas
            : StatusTagihan::Klaim;

        $tagihan->update([
            'status' => $statusBaru,
            'finalized_by' => $userId,
            'finalized_at' => now(),
        ]);

        $kunjungan->update(['status' => StatusKunjungan::MenungguPembayaran]);

        return $tagihan->fresh();
    }

    /**
     * Catat pembayaran (bisa partial). Status otomatis ke LUNAS kalau total terpenuhi.
     */
    public function catatPembayaran(
        Tagihan $tagihan,
        MetodePembayaran $metode,
        float $jumlah,
        string $kasirId,
        ?string $referensi = null,
        ?string $catatan = null,
    ): Pembayaran {
        return DB::transaction(function () use ($tagihan, $metode, $jumlah, $kasirId, $referensi, $catatan) {
            if (in_array($tagihan->status, [StatusTagihan::Lunas, StatusTagihan::Void])) {
                throw new \DomainException('Tagihan tidak bisa dibayar lagi (status: ' . $tagihan->status->label() . ').');
            }

            $pembayaran = Pembayaran::create([
                'no_pembayaran' => Pembayaran::generateNoPembayaran(),
                'tagihan_id' => $tagihan->id,
                'tgl_bayar' => now(),
                'metode' => $metode,
                'jumlah' => $jumlah,
                'referensi_eksternal' => $referensi,
                'kasir_id' => $kasirId,
                'catatan' => $catatan,
            ]);

            $this->hitungUlangTotal($tagihan);

            // Jika sisa = 0 → lunas, dan kunjungan selesai
            $tagihan->refresh();
            if ((float) $tagihan->sisa <= 0) {
                $tagihan->update(['status' => StatusTagihan::Lunas]);
                $tagihan->kunjungan->update([
                    'status' => StatusKunjungan::Selesai,
                    'tgl_keluar' => $tagihan->kunjungan->tgl_keluar ?: now(),
                ]);
            } elseif ($tagihan->status === StatusTagihan::BelumLunas) {
                $tagihan->update(['status' => StatusTagihan::Cicilan]);
            }

            return $pembayaran;
        });
    }

    protected function tambahDetail(Tagihan $tagihan, array $data): void
    {
        TagihanDetail::create(array_merge(
            ['tagihan_id' => $tagihan->id, 'diskon' => 0],
            $data,
        ));
    }

    /**
     * Hitung ulang total tagihan dari details + sisa dari pembayaran.
     */
    protected function hitungUlangTotal(Tagihan $tagihan): void
    {
        $subtotal = (float) $tagihan->details()->sum('subtotal');
        $diskon = (float) $tagihan->diskon;
        $ppn = (float) $tagihan->ppn;
        $total = $subtotal - $diskon + $ppn;
        $dibayar = (float) $tagihan->pembayaran()->sum('jumlah');
        $sisa = max(0, $total - $dibayar);

        $tagihan->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'dibayar' => $dibayar,
            'sisa' => $sisa,
        ]);
    }
}
