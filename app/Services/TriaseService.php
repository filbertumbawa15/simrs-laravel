<?php

namespace App\Services;

use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\TriaseIgd;
use Illuminate\Support\Facades\DB;

class TriaseService
{
    /**
     * Lakukan triase awal pasien IGD. Wajib dilakukan dalam 5 menit
     * sejak pasien tiba (standar accreditation KARS/JCI).
     *
     * Kategori:
     * - MERAH  → Resusitasi, ditangani SEGERA (target 0 menit)
     * - KUNING → Emergent, ditangani < 10 menit
     * - HIJAU  → Urgent, < 30 menit
     * - HITAM  → DOA (Death on Arrival)
     */
    public function triase(
        Kunjungan $kunjungan,
        string $kategori,
        string $keluhanUtama,
        array $tandaVital,
        string $petugasId,
    ): TriaseIgd {
        return DB::transaction(function () use ($kunjungan, $kategori, $keluhanUtama, $tandaVital, $petugasId) {

            if ($kunjungan->tipe !== TipeKunjungan::IGD) {
                throw new \DomainException('Triase hanya untuk kunjungan IGD.');
            }

            if ($kunjungan->triase) {
                throw new \DomainException('Pasien ini sudah ditriase. Update via fitur re-triase.');
            }

            $triase = TriaseIgd::create([
                'kunjungan_id' => $kunjungan->id,
                'kategori' => $kategori,
                'waktu_triase' => now(),
                'triase_oleh' => $petugasId,
                'keluhan_utama' => $keluhanUtama,
                'tanda_vital' => $tandaVital,
            ]);

            $kunjungan->update(['status' => StatusKunjungan::DalamPemeriksaan]);

            return $triase;
        });
    }
}
