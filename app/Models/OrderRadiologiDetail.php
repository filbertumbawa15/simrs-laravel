<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRadiologiDetail extends Model
{
    use HasUuid;

    protected $table = 'order_radiologi_detail';

    protected $fillable = ['order_id', 'pemeriksaan_id', 'tarif'];

    protected function casts(): array
    {
        return ['tarif' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderRadiologi::class, 'order_id');
    }

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(PemeriksaanRadiologi::class, 'pemeriksaan_id');
    }
}
