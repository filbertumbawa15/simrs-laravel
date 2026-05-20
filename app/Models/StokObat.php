<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokObat extends Model
{
    use HasUuid;

    protected $table = 'stok_obat';

    protected $fillable = [
        'obat_id',
        'no_batch',
        'jumlah_masuk',
        'jumlah_sisa',
        'tgl_masuk',
        'exp_date',
        'hpp',
        'supplier',
        'no_faktur',
    ];

    protected function casts(): array
    {
        return [
            'tgl_masuk' => 'date',
            'exp_date' => 'date',
            'hpp' => 'decimal:2',
        ];
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    /**
     * Scope FEFO — First Expired First Out. Batch dengan exp paling dekat keluar duluan.
     */
    public function scopeFefo($query)
    {
        return $query->where('jumlah_sisa', '>', 0)
            ->whereDate('exp_date', '>', now())
            ->orderBy('exp_date');
    }

    public function scopeWillExpireSoon($query, int $days = 90)
    {
        return $query->where('jumlah_sisa', '>', 0)
            ->whereDate('exp_date', '<=', now()->addDays($days));
    }
}
