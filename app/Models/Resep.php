<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    use HasUuid;

    protected $table = 'resep';

    protected $fillable = [
        'no_resep',
        'kunjungan_id',
        'dokter_id',
        'tgl_resep',
        'status',
        'is_racikan',
        'catatan',
        'apoteker_verifikator_id',
        'verified_at',
        'penyerah_id',
        'diserahkan_at',
    ];

    protected function casts(): array
    {
        return [
            'tgl_resep' => 'datetime',
            'verified_at' => 'datetime',
            'diserahkan_at' => 'datetime',
            'is_racikan' => 'boolean',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ResepDetail::class);
    }

    public function apoteker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'apoteker_verifikator_id');
    }

    public function penyerah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyerah_id');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->details->sum('subtotal');
    }

    public static function generateNoResep(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "RX/{$year}/{$month}";

        $last = self::where('no_resep', 'like', "{$prefix}/%")
            ->orderByDesc('no_resep')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->no_resep, -5)) + 1 : 1;

        return sprintf('%s/%05d', $prefix, $seq);
    }
}
