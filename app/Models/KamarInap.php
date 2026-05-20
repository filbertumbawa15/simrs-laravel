<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KamarInap extends Model
{
    use HasUuid;

    protected $table = 'kamar_inap';

    protected $fillable = [
        'rawat_inap_id',
        'kamar_id',
        'masuk',
        'keluar',
        'alasan_pindah',
    ];

    protected function casts(): array
    {
        return [
            'masuk' => 'datetime',
            'keluar' => 'datetime',
        ];
    }

    public function rawatInap(): BelongsTo
    {
        return $this->belongsTo(RawatInap::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function getDurasiHariAttribute(): int
    {
        $end = $this->keluar ?: now();

        return max(1, $this->masuk->diffInDays($end));
    }
}
