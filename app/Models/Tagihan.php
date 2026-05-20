<?php

namespace App\Models;

use App\Enums\StatusTagihan;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tagihan extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'tagihan';

    protected $fillable = [
        'no_tagihan',
        'kunjungan_id',
        'tgl_tagihan',
        'subtotal',
        'diskon',
        'ppn',
        'total',
        'dibayar',
        'sisa',
        'klaim_penjamin',
        'iur_pasien',
        'status',
        'finalized_by',
        'finalized_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_tagihan' => 'datetime',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'ppn' => 'decimal:2',
            'total' => 'decimal:2',
            'dibayar' => 'decimal:2',
            'sisa' => 'decimal:2',
            'klaim_penjamin' => 'decimal:2',
            'iur_pasien' => 'decimal:2',
            'finalized_at' => 'datetime',
            'status' => StatusTagihan::class,
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TagihanDetail::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class)->where('is_void', false);
    }

    public function klaimBpjs(): HasOne
    {
        return $this->hasOne(KlaimBpjs::class);
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public static function generateNoTagihan(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "INV/{$year}/{$month}";

        $last = self::where('no_tagihan', 'like', "{$prefix}/%")
            ->orderByDesc('no_tagihan')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->no_tagihan, -5)) + 1 : 1;

        return sprintf('%s/%05d', $prefix, $seq);
    }
}
