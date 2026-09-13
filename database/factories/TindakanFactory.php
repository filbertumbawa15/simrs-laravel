<?php

namespace Database\Factories;

use App\Models\Tindakan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tindakan>
 */
class TindakanFactory extends Factory
{
    protected $model = Tindakan::class;

    public function definition(): array
    {
        return [
            'kode' => 'TDK' . fake()->unique()->numerify('####'),
            'nama' => fake()->randomElement(['Pemeriksaan Fisik', 'Injeksi IM', 'Infus RL', 'Nebulizer', 'EKG', 'Jahit Luka']),
            'kategori' => fake()->randomElement(['Umum', 'Bedah Minor', 'Terapi']),
            'tarif_vip' => 100000,
            'tarif_kelas1' => 80000,
            'tarif_kelas2' => 60000,
            'tarif_kelas3' => 40000,
            'is_active' => true,
        ];
    }
}
