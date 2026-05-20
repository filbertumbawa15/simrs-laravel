<?php

namespace App\Services;

use App\Models\MutasiStokObat;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\StokObat;
use Illuminate\Support\Facades\DB;

class FarmasiService
{
    /**
     * Buat resep baru. Hitung subtotal otomatis berdasarkan harga jual saat ini.
     *
     * @param  array  $items  Array of ['obat_id', 'jumlah', 'signa', 'aturan_pakai', 'catatan']
     */
    public function buatResep(string $kunjunganId, string $dokterId, array $items, ?string $catatan = null): Resep
    {
        return DB::transaction(function () use ($kunjunganId, $dokterId, $items, $catatan) {
            $resep = Resep::create([
                'no_resep' => Resep::generateNoResep(),
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'tgl_resep' => now(),
                'status' => 'BARU',
                'catatan' => $catatan,
            ]);

            foreach ($items as $item) {
                $obat = Obat::findOrFail($item['obat_id']);

                ResepDetail::create([
                    'resep_id' => $resep->id,
                    'obat_id' => $obat->id,
                    'jumlah' => $item['jumlah'],
                    'signa' => $item['signa'],
                    'aturan_pakai' => $item['aturan_pakai'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                    'harga_satuan' => $obat->harga_jual,
                    'subtotal' => $obat->harga_jual * $item['jumlah'],
                ]);
            }

            return $resep->load('details');
        });
    }

    /**
     * Apoteker verifikasi resep — cek interaksi obat, dosis, dst.
     */
    public function verifikasiResep(Resep $resep, string $apotekerId): Resep
    {
        $resep->update([
            'status' => 'DIVERIFIKASI',
            'apoteker_verifikator_id' => $apotekerId,
            'verified_at' => now(),
        ]);

        return $resep->fresh();
    }

    /**
     * Serahkan obat ke pasien. Otomatis kurangi stok pakai FEFO,
     * catat mutasi stok per batch yang dipakai.
     *
     * @throws \DomainException kalau stok tidak cukup
     */
    public function serahkanObat(Resep $resep, string $penyerahId): Resep
    {
        return DB::transaction(function () use ($resep, $penyerahId) {
            foreach ($resep->details as $detail) {
                $this->keluarkanStokFefo($detail, $penyerahId);
            }

            $resep->update([
                'status' => 'DISERAHKAN',
                'penyerah_id' => $penyerahId,
                'diserahkan_at' => now(),
            ]);

            return $resep->fresh('details');
        });
    }

    /**
     * Logic FEFO: keluarkan stok dari batch yang paling cepat expired.
     * Jika 1 batch tidak cukup, split ke beberapa batch.
     */
    protected function keluarkanStokFefo(ResepDetail $detail, string $userId): void
    {
        $sisaButuh = $detail->jumlah;
        $batchUsed = [];

        $stoks = StokObat::where('obat_id', $detail->obat_id)
            ->fefo()
            ->lockForUpdate()
            ->get();

        $totalTersedia = $stoks->sum('jumlah_sisa');
        if ($totalTersedia < $sisaButuh) {
            throw new \DomainException(
                "Stok {$detail->obat->nama} tidak cukup. Butuh {$detail->jumlah}, tersedia {$totalTersedia}."
            );
        }

        foreach ($stoks as $stok) {
            if ($sisaButuh <= 0) {
                break;
            }

            $ambil = min($stok->jumlah_sisa, $sisaButuh);
            $saldoSebelum = $stok->jumlah_sisa;
            $stok->decrement('jumlah_sisa', $ambil);
            $sisaButuh -= $ambil;

            MutasiStokObat::create([
                'stok_id' => $stok->id,
                'obat_id' => $detail->obat_id,
                'jenis' => 'KELUAR',
                'resep_detail_id' => $detail->id,
                'jumlah' => -$ambil,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSebelum - $ambil,
                'referensi' => "Resep {$detail->resep->no_resep}",
                'user_id' => $userId,
            ]);

            $batchUsed[] = [
                'batch' => $stok->no_batch,
                'qty' => $ambil,
                'exp_date' => $stok->exp_date->toDateString(),
            ];
        }

        $detail->update([
            'is_diserahkan' => true,
            'batch_used' => $batchUsed,
        ]);
    }
}
