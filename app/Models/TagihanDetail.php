<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TagihanDetail extends Model
{
    use HasUuid;

    protected $table = 'tagihan_detail';

    protected $fillable = [
        'tagihan_id',
        'kategori',
        'referensi_type',
        'referensi_id',
        'deskripsi',
        'qty',
        'harga',
        'diskon',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'diskon' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}
