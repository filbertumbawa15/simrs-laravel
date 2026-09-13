<?php

namespace Database\Factories;

use App\Models\Dokter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dokter>
 */
class DokterFactory extends Factory
{
    protected $model = Dokter::class;

    public function definition(): array
    {
        return [
            'kode' => 'DR' . fake()->unique()->numerify('#####'),
            'sip' => fake()->unique()->numerify('###/SIP/####/####'),
            'nik' => fake()->unique()->numerify('################'),
            'nama' => fake()->name(),
            'gelar_depan' => 'dr.',
            'gelar_belakang' => fake()->randomElement([null, 'Sp.PD', 'Sp.A', 'Sp.OG', 'Sp.B', 'Sp.PK', 'Sp.Rad']),
            'spesialisasi' => fake()->randomElement(['Umum', 'Penyakit Dalam', 'Anak', 'Bedah', 'OBG', 'Patologi Klinik', 'Radiologi']),
            'telp' => '08' . fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'jasa_konsul' => fake()->randomElement([50000, 75000, 100000, 150000, 200000]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
