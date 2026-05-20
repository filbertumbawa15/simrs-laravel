<?php

namespace App\Services;

use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Support\Facades\DB;

class PendaftaranService
{
    /**
     * Daftarkan pasien baru. Otomatis generate no_rm dan buat rekam_medis.
     */
    public function daftarPasienBaru(array $data): Pasien
    {
        return DB::transaction(function () use ($data) {
            $data['no_rm'] = Pasien::generateNoRm();
            $pasien = Pasien::create($data);

            // Setiap pasien wajib punya 1 rekam medis (1:1)
            RekamMedis::create(['pasien_id' => $pasien->id]);

            return $pasien;
        });
    }

    /**
     * Buat kunjungan baru untuk pasien existing.
     */
    public function buatKunjungan(Pasien $pasien, array $data): Kunjungan
    {
        return DB::transaction(function () use ($pasien, $data) {
            $tipe = TipeKunjungan::from($data['tipe']);

            // Cek apakah pasien masih punya kunjungan aktif
            $aktif = $pasien->kunjungan()->aktif()->exists();
            if ($aktif) {
                throw new \DomainException(
                    'Pasien masih memiliki kunjungan aktif. Selesaikan kunjungan sebelumnya terlebih dahulu.'
                );
            }

            return Kunjungan::create([
                'no_kunjungan' => Kunjungan::generateNoKunjungan($tipe),
                'pasien_id' => $pasien->id,
                'tipe' => $tipe,
                'tgl_masuk' => now(),
                'status' => StatusKunjungan::Terdaftar,
                'penjamin' => Penjamin::from($data['penjamin'] ?? 'UMUM'),
                'asuransi_pasien_id' => $data['asuransi_pasien_id'] ?? null,
                'no_rujukan' => $data['no_rujukan'] ?? null,
                'no_sep' => $data['no_sep'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
