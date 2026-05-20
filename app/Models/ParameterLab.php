<?php

namespace App\Models;

use App\Enums\FlagHasilLab;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParameterLab extends Model
{
    use HasUuid;

    protected $table = 'parameter_lab';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'rujukan_normal',
        'nilai_rujukan_min',
        'nilai_rujukan_max',
        'nilai_kritis_low',
        'nilai_kritis_high',
        'tipe_hasil',
        'loinc_code',
        'tarif',
        'tat_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nilai_rujukan_min' => 'decimal:4',
            'nilai_rujukan_max' => 'decimal:4',
            'nilai_kritis_low' => 'decimal:4',
            'nilai_kritis_high' => 'decimal:4',
            'tarif' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function paket(): BelongsToMany
    {
        return $this->belongsToMany(PaketLab::class, 'paket_lab_parameter', 'parameter_id', 'paket_id');
    }

    /**
     * Flag a numeric value against this parameter's reference range.
     */
    public function evaluateFlag(?float $value): FlagHasilLab
    {
        if ($value === null) {
            return FlagHasilLab::Normal;
        }

        if ($this->nilai_kritis_low !== null && $value <= (float) $this->nilai_kritis_low) {
            return FlagHasilLab::CriticalLow;
        }
        if ($this->nilai_kritis_high !== null && $value >= (float) $this->nilai_kritis_high) {
            return FlagHasilLab::CriticalHigh;
        }
        if ($this->nilai_rujukan_min !== null && $value < (float) $this->nilai_rujukan_min) {
            return FlagHasilLab::Low;
        }
        if ($this->nilai_rujukan_max !== null && $value > (float) $this->nilai_rujukan_max) {
            return FlagHasilLab::High;
        }

        return FlagHasilLab::Normal;
    }
}
