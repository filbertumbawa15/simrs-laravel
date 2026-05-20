<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResepDetail extends Model
{
    use HasUuid;

    protected $table = 'resep_detail';

    protected $fillable = [
        'resep_id',
        'obat_id',
        'jumlah',
        'signa',
        'aturan_pakai',
        'catatan',
        'harga_satuan',
        'subtotal',
        'is_diserahkan',
        'batch_used',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'is_diserahkan' => 'boolean',
            'batch_used' => 'array',
        ];
    }

    public function resep(): BelongsTo
    {
        return $this->belongsTo(Resep::class);
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }
}
