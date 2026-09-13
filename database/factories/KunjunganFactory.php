<?php

namespace Database\Factories;

use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kunjungan>
 */
class KunjunganFactory extends Factory
{
    protected $model = Kunjungan::class;

    public function definition(): array
    {
        $tipe = fake()->randomElement(TipeKunjungan::cases());

        return [
            'no_kunjungan' => $tipe->value . '/' . now()->format('Y/m') . '/' . fake()->unique()->numerify('#####'),
            'pasien_id' => Pasien::factory(),
            'tipe' => $tipe,
            'tgl_masuk' => now(),
            'status' => StatusKunjungan::Terdaftar,
            'penjamin' => Penjamin::Umum,
        ];
    }

    public function rj(): static
    {
        return $this->state(fn () => [
            'tipe' => TipeKunjungan::RawatJalan,
            'no_kunjungan' => 'RJ/' . now()->format('Y/m') . '/' . fake()->unique()->numerify('#####'),
        ]);
    }

    public function ri(): static
    {
        return $this->state(fn () => [
            'tipe' => TipeKunjungan::RawatInap,
            'no_kunjungan' => 'RI/' . now()->format('Y/m') . '/' . fake()->unique()->numerify('#####'),
        ]);
    }

    public function igd(): static
    {
        return $this->state(fn () => [
            'tipe' => TipeKunjungan::IGD,
            'no_kunjungan' => 'IGD/' . now()->format('Y/m') . '/' . fake()->unique()->numerify('#####'),
        ]);
    }

    public function bpjs(): static
    {
        return $this->state(fn () => ['penjamin' => Penjamin::BPJS]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'status' => StatusKunjungan::Selesai,
            'tgl_keluar' => now(),
        ]);
    }
}
