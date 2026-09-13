<?php

namespace Database\Factories;

use App\Models\Obat;
use App\Models\StokObat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StokObat>
 */
class StokObatFactory extends Factory
{
    protected $model = StokObat::class;

    public function definition(): array
    {
        $jumlah = fake()->numberBetween(100, 500);

        return [
            'obat_id' => Obat::factory(),
            'no_batch' => 'BATCH' . fake()->unique()->numerify('#####'),
            'jumlah_masuk' => $jumlah,
            'jumlah_sisa' => $jumlah,
            'tgl_masuk' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'exp_date' => now()->addMonths(fake()->numberBetween(6, 36))->toDateString(),
            'hpp' => fake()->randomElement([400, 800, 2000, 4000, 8000]),
            'supplier' => fake()->company(),
            'no_faktur' => 'INV-' . fake()->unique()->numerify('#####'),
        ];
    }

    public function forObat(Obat $obat): static
    {
        return $this->state(fn () => ['obat_id' => $obat->id]);
    }

    public function withBatch(string $batch, int $jumlah, string $expDate): static
    {
        return $this->state(fn () => [
            'no_batch' => $batch,
            'jumlah_masuk' => $jumlah,
            'jumlah_sisa' => $jumlah,
            'exp_date' => $expDate,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'exp_date' => now()->subMonths(1)->toDateString(),
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn () => ['jumlah_sisa' => 0]);
    }
}
