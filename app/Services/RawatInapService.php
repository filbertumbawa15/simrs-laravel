<?php

namespace App\Services;

use App\Enums\StatusKamar;
use App\Enums\StatusKunjungan;
use App\Models\Kamar;
use App\Models\KamarInap;
use App\Models\Kunjungan;
use App\Models\RawatInap;
use Illuminate\Support\Facades\DB;

class RawatInapService
{
    /**
     * Admisi pasien ke kamar. Validasi: kamar harus TERSEDIA & aktif.
     * Update status kamar → TERISI dalam transaksi.
     */
    public function admisi(
        Kunjungan $kunjungan,
        string $kamarId,
        string $dpjpId,
        string $alasanMasuk,
    ): RawatInap {
        return DB::transaction(function () use ($kunjungan, $kamarId, $dpjpId, $alasanMasuk) {

            if ($kunjungan->tipe->value !== 'RI' && $kunjungan->tipe->value !== 'IGD') {
                throw new \DomainException(
                    'Hanya kunjungan tipe RI atau IGD (yang dilanjutkan ke RI) yang bisa di-admisi.'
                );
            }

            // Cek apakah sudah ada RI untuk kunjungan ini
            if ($kunjungan->rawatInap) {
                throw new \DomainException('Kunjungan ini sudah memiliki rawat inap aktif.');
            }

            // Lock kamar untuk hindari race condition
            $kamar = Kamar::where('id', $kamarId)->lockForUpdate()->firstOrFail();

            if ($kamar->status !== StatusKamar::Tersedia || ! $kamar->is_active) {
                throw new \DomainException(
                    "Kamar {$kamar->no_kamar} tidak tersedia (status: {$kamar->status->label()})."
                );
            }

            // Buat record RI
            $ri = RawatInap::create([
                'kunjungan_id' => $kunjungan->id,
                'dpjp_id' => $dpjpId,
                'tgl_masuk_ri' => now(),
                'alasan_masuk' => $alasanMasuk,
            ]);

            // Catat penempatan kamar pertama
            KamarInap::create([
                'rawat_inap_id' => $ri->id,
                'kamar_id' => $kamar->id,
                'masuk' => now(),
            ]);

            // Update status kamar
            $kamar->update(['status' => StatusKamar::Terisi]);

            // Update tipe kunjungan (kalau dari IGD) & status
            $kunjungan->update([
                'tipe' => 'RI',
                'status' => StatusKunjungan::DalamPemeriksaan,
            ]);

            return $ri->fresh(['dpjp', 'kamarInap.kamar.kelas']);
        });
    }

    /**
     * Pindahkan pasien ke kamar lain. Kamar lama → KOTOR (perlu cleaning),
     * kamar baru → TERISI.
     */
    public function pindahKamar(
        RawatInap $ri,
        string $kamarBaruId,
        ?string $alasan = null,
    ): KamarInap {
        return DB::transaction(function () use ($ri, $kamarBaruId, $alasan) {
            $kamarBaru = Kamar::where('id', $kamarBaruId)->lockForUpdate()->firstOrFail();

            if ($kamarBaru->status !== StatusKamar::Tersedia) {
                throw new \DomainException(
                    "Kamar {$kamarBaru->no_kamar} tidak tersedia."
                );
            }

            // Tutup penempatan kamar lama
            $kamarLamaInap = $ri->kamarInap()->whereNull('keluar')->lockForUpdate()->first();
            if ($kamarLamaInap) {
                $kamarLamaInap->update([
                    'keluar' => now(),
                    'alasan_pindah' => $alasan,
                ]);

                // Kamar lama → KOTOR (perlu cleaning service)
                $kamarLamaInap->kamar->update(['status' => StatusKamar::Kotor]);
            }

            // Buka penempatan kamar baru
            $kamarBaruInap = KamarInap::create([
                'rawat_inap_id' => $ri->id,
                'kamar_id' => $kamarBaru->id,
                'masuk' => now(),
            ]);

            $kamarBaru->update(['status' => StatusKamar::Terisi]);

            return $kamarBaruInap->fresh('kamar.kelas');
        });
    }

    /**
     * Pasien pulang. Validasi: resume medis harus diisi.
     */
    public function pulang(
        RawatInap $ri,
        string $caraPulang,
        string $resumeMedis,
        ?string $instruksiPulang = null,
    ): RawatInap {
        return DB::transaction(function () use ($ri, $caraPulang, $resumeMedis, $instruksiPulang) {
            if ($ri->tgl_pulang) {
                throw new \DomainException('Pasien sudah pulang sebelumnya.');
            }

            if (empty(trim($resumeMedis))) {
                throw new \DomainException('Resume medis wajib diisi sebelum pasien pulang.');
            }

            // Tutup penempatan kamar terakhir
            $kamarInap = $ri->kamarInap()->whereNull('keluar')->lockForUpdate()->first();
            if ($kamarInap) {
                $kamarInap->update(['keluar' => now()]);
                $kamarInap->kamar->update(['status' => StatusKamar::Kotor]);
            }

            // Finalisasi RI
            $ri->update([
                'tgl_pulang' => now(),
                'cara_pulang' => $caraPulang,
                'resume_medis' => $resumeMedis,
                'instruksi_pulang' => $instruksiPulang,
                'resume_finalized' => true,
                'resume_finalized_at' => now(),
            ]);

            // Update kunjungan → menunggu pembayaran
            $ri->kunjungan->update([
                'status' => StatusKunjungan::MenungguPembayaran,
                'tgl_keluar' => now(),
            ]);

            return $ri->fresh();
        });
    }
}
