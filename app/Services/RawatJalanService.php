<?php

namespace App\Services;

use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\RawatJalan;
use Illuminate\Support\Facades\DB;

class RawatJalanService
{
    /**
     * Assign pasien ke poli, generate no antrian.
     */
    public function assignKePoli(Kunjungan $kunjungan, string $poliId, string $dokterId): RawatJalan
    {
        return DB::transaction(function () use ($kunjungan, $poliId, $dokterId) {
            // Kunci row terakhir di poli + tanggal yang sama untuk hindari race condition
            $today = $kunjungan->tgl_masuk->toDateString();

            $lastAntrian = RawatJalan::where('poli_id', $poliId)
                ->whereDate('created_at', $today)
                ->lockForUpdate()
                ->max('no_antrian');

            $rj = RawatJalan::create([
                'kunjungan_id' => $kunjungan->id,
                'poli_id' => $poliId,
                'dokter_id' => $dokterId,
                'no_antrian' => ($lastAntrian ?? 0) + 1,
            ]);

            $kunjungan->update(['status' => StatusKunjungan::Terdaftar]);

            return $rj;
        });
    }

    /**
     * Panggil pasien (klik tombol "Panggil" di antrian).
     */
    public function panggilPasien(RawatJalan $rj): RawatJalan
    {
        $rj->update(['waktu_panggilan' => now()]);
        $rj->kunjungan->update(['status' => StatusKunjungan::DalamPemeriksaan]);

        return $rj->fresh();
    }

    /**
     * Mulai periksa.
     */
    public function mulaiPeriksa(RawatJalan $rj): RawatJalan
    {
        if (! $rj->waktu_mulai_periksa) {
            $rj->update(['waktu_mulai_periksa' => now()]);
        }

        return $rj->fresh();
    }

    /**
     * Simpan SOAP. Idempotent — bisa di-save berkali-kali sampai selesai.
     */
    public function simpanSoap(RawatJalan $rj, array $data): RawatJalan
    {
        $rj->update([
            'subjective' => $data['subjective'] ?? null,
            'objective' => $data['objective'] ?? null,
            'tanda_vital' => $data['tanda_vital'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'edukasi' => $data['edukasi'] ?? null,
        ]);

        return $rj->fresh();
    }

    /**
     * Selesaikan pemeriksaan RJ. Kunjungan masih bisa lanjut ke farmasi/lab.
     */
    public function selesaikanPemeriksaan(RawatJalan $rj): RawatJalan
    {
        $rj->update(['waktu_selesai_periksa' => now()]);

        // Status kunjungan tergantung apakah ada order lab / resep
        $kunjungan = $rj->kunjungan;
        $adaLab = $kunjungan->orderLab()->exists();
        $adaResep = $kunjungan->resep()->exists();

        $statusBaru = match (true) {
            $adaLab => StatusKunjungan::MenungguHasilLab,
            $adaResep => StatusKunjungan::MenungguObat,
            default => StatusKunjungan::MenungguPembayaran,
        };

        $kunjungan->update(['status' => $statusBaru]);

        return $rj->fresh();
    }
}
