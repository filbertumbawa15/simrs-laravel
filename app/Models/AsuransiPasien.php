<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsuransiPasien extends Model
{
    use HasUuid;

    protected $table = 'asuransi_pasien';

    protected $fillable = [
        'pasien_id',
        'asuransi_id',
        'no_polis',
        'nama_pemegang',
        'valid_from',
        'valid_until',
        'kelas_hak',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function asuransi(): BelongsTo
    {
        return $this->belongsTo(Asuransi::class);
    }
}
