<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaketLab extends Model
{
    use HasUuid;

    protected $table = 'paket_lab';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'tarif', 'is_active'];

    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function parameter(): BelongsToMany
    {
        return $this->belongsToMany(ParameterLab::class, 'paket_lab_parameter', 'paket_id', 'parameter_id');
    }
}
