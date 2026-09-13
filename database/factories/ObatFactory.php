<?php

namespace Database\Factories;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obat>
 */
class ObatFactory extends Factory
{
    protected $model = Obat::class;

    public function definition(): array
    {
        return [
            'kode' => 'OBT' . fake()->unique()->numerify('#####'),
            'nama' => fake()->randomElement([
                'Paracetamol 500mg', 'Amoxicillin 500mg', 'Ibuprofen 400mg',
                'Omeprazole 20mg', 'Cetirizine 10mg', 'Amlodipine 5mg',
            ]) . ' ' . fake()->numerify('#####'),
            'nama_generik' => fake()->word(),
            'golongan' => fake()->randomElement(['BEBAS', 'BEBAS_TERBATAS', 'KERAS']),
            'bentuk_sediaan' => 'Tablet',
            'satuan' => 'Tablet',
            'kekuatan' => '500mg',
            'harga_jual' => fake()->randomElement([500, 1000, 2500, 5000, 10000]),
            'stok_minimum' => 20,
            'is_fornas' => true,
            'is_active' => true,
        ];
    }

    public function narkotika(): static
    {
        return $this->state(fn () => ['golongan' => 'NARKOTIKA']);
    }
}
