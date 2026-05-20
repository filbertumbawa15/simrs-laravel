<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasUuid;

    protected $table = 'pembayaran';

    protected $fillable = [
        'no_pembayaran',
        'tagihan_id',
        'tgl_bayar',
        'metode',
        'jumlah',
        'referensi_eksternal',
        'kasir_id',
        'catatan',
        'is_void',
        'void_by',
        'void_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'tgl_bayar' => 'datetime',
            'metode' => MetodePembayaran::class,
            'jumlah' => 'decimal:2',
            'is_void' => 'boolean',
            'void_at' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public static function generateNoPembayaran(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "PB/{$year}/{$month}";

        $last = self::where('no_pembayaran', 'like', "{$prefix}/%")
            ->orderByDesc('no_pembayaran')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->no_pembayaran, -6)) + 1 : 1;

        return sprintf('%s/%06d', $prefix, $seq);
    }
}
