<?php

namespace Database\Factories;

use App\Models\ParameterLab;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParameterLab>
 */
class ParameterLabFactory extends Factory
{
    protected $model = ParameterLab::class;

    public function definition(): array
    {
        return [
            'kode' => 'LAB' . fake()->unique()->numerify('####'),
            'nama' => fake()->randomElement(['Hemoglobin', 'Leukosit', 'GDS', 'Kolesterol', 'Kreatinin']),
            'kategori' => 'Kimia Klinik',
            'satuan' => 'mg/dL',
            'rujukan_normal' => '70-140',
            'nilai_rujukan_min' => 70,
            'nilai_rujukan_max' => 140,
            'nilai_kritis_low' => 40,
            'nilai_kritis_high' => 400,
            'tipe_hasil' => 'NUMERIK',
            'tarif' => 25000,
            'tat_minutes' => 60,
            'is_active' => true,
        ];
    }

    /**
     * Preset untuk hemoglobin (nilai kritis low 7).
     */
    public function hemoglobin(): static
    {
        return $this->state(fn () => [
            'nama' => 'Hemoglobin',
            'satuan' => 'g/dL',
            'rujukan_normal' => '12-16',
            'nilai_rujukan_min' => 12,
            'nilai_rujukan_max' => 16,
            'nilai_kritis_low' => 7,
            'nilai_kritis_high' => 20,
        ]);
    }
}
