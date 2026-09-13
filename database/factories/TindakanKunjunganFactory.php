<?php

namespace Database\Factories;

use App\Models\Kunjungan;
use App\Models\Tindakan;
use App\Models\TindakanKunjungan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TindakanKunjungan>
 */
class TindakanKunjunganFactory extends Factory
{
    protected $model = TindakanKunjungan::class;

    public function definition(): array
    {
        $tarif = 60000;

        return [
            'kunjungan_id' => Kunjungan::factory(),
            'tindakan_id' => Tindakan::factory(),
            'petugas_id' => User::factory(),
            'waktu_tindakan' => now(),
            'qty' => 1,
            'tarif' => $tarif,
            'subtotal' => $tarif,
        ];
    }
}
