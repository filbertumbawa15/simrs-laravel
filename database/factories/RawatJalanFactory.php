<?php

namespace Database\Factories;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\RawatJalan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RawatJalan>
 */
class RawatJalanFactory extends Factory
{
    protected $model = RawatJalan::class;

    public function definition(): array
    {
        return [
            'kunjungan_id' => Kunjungan::factory()->rj(),
            'poli_id' => Poli::factory(),
            'dokter_id' => Dokter::factory(),
            'no_antrian' => fake()->numberBetween(1, 30),
        ];
    }
}
