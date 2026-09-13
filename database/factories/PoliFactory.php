<?php

namespace Database\Factories;

use App\Models\Poli;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poli>
 */
class PoliFactory extends Factory
{
    protected $model = Poli::class;

    public function definition(): array
    {
        return [
            'kode' => 'PL' . fake()->unique()->numerify('###'),
            'nama' => 'Poli ' . fake()->randomElement(['Umum', 'Anak', 'Penyakit Dalam', 'OBG', 'Bedah', 'Mata']),
            'lokasi' => 'Lantai ' . fake()->numberBetween(1, 3),
            'is_active' => true,
        ];
    }
}
