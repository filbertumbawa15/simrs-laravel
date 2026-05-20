<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Tindakan extends Model
{
    use HasUuid;

    protected $table = 'tindakan';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'icd9_kode',
        'tarif_vip',
        'tarif_kelas1',
        'tarif_kelas2',
        'tarif_kelas3',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tarif_vip' => 'decimal:2',
            'tarif_kelas1' => 'decimal:2',
            'tarif_kelas2' => 'decimal:2',
            'tarif_kelas3' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Return tarif sesuai kelas kamar.
     */
    public function tarifByKelas(?string $kelas): float
    {
        return (float) match (strtoupper($kelas ?? '')) {
            'VIP' => $this->tarif_vip,
            'I', 'KELAS1' => $this->tarif_kelas1,
            'II', 'KELAS2' => $this->tarif_kelas2,
            'III', 'KELAS3' => $this->tarif_kelas3,
            default => $this->tarif_kelas3,
        };
    }
}
