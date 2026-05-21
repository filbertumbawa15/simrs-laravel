<?php

namespace App\Models;

use App\Enums\Modalitas;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanRadiologi extends Model
{
    use HasUuid;

    protected $table = 'pemeriksaan_radiologi';

    protected $fillable = [
        'kode', 'nama', 'modalitas', 'region',
        'tarif_vip', 'tarif_kelas1', 'tarif_kelas2', 'tarif_kelas3',
        'template_bacaan', 'tat_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'modalitas' => Modalitas::class,
            'tarif_vip' => 'decimal:2',
            'tarif_kelas1' => 'decimal:2',
            'tarif_kelas2' => 'decimal:2',
            'tarif_kelas3' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function tarifByKelas(?string $kelas): float
    {
        return (float) match (strtoupper($kelas ?? '')) {
            'VIP' => $this->tarif_vip,
            'I' => $this->tarif_kelas1,
            'II' => $this->tarif_kelas2,
            'III' => $this->tarif_kelas3,
            default => $this->tarif_kelas3,
        };
    }
}
