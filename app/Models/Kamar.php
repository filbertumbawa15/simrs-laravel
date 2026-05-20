<?php

namespace App\Models;

use App\Enums\StatusKamar;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'kamar';

    protected $fillable = [
        'no_kamar',
        'kelas_id',
        'status',
        'lokasi',
        'kapasitas',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusKamar::class,
            'is_active' => 'boolean',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasKamar::class, 'kelas_id');
    }

    public function kamarInap(): HasMany
    {
        return $this->hasMany(KamarInap::class);
    }

    public function scopeTersedia($query)
    {
        return $query->where('status', StatusKamar::Tersedia)->where('is_active', true);
    }
}
