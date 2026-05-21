<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilRadiologi extends Model
{
    use HasUuid;

    protected $table = 'hasil_radiologi';

    protected $fillable = [
        'order_id', 'pemeriksaan_id', 'bacaan', 'kesan', 'saran',
        'ada_temuan_kritis', 'critical_notified', 'critical_notified_at',
        'radiolog_id', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'ada_temuan_kritis' => 'boolean',
            'critical_notified' => 'boolean',
            'critical_notified_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderRadiologi::class, 'order_id');
    }

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(PemeriksaanRadiologi::class, 'pemeriksaan_id');
    }

    public function radiolog(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radiolog_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HasilRadiologiImage::class, 'hasil_id');
    }
}
