<?php

namespace App\Models;

use App\Enums\PrioritasOrder;
use App\Enums\StatusOrderLab;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderLab extends Model
{
    use HasUuid;

    protected $table = 'order_lab';

    protected $fillable = [
        'no_order',
        'kunjungan_id',
        'dokter_id',
        'tgl_order',
        'prioritas',
        'status',
        'catatan_klinis',
        'diagnosa_kerja',
        'sampling_oleh',
        'sampling_at',
        'validator_id',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'tgl_order' => 'datetime',
            'sampling_at' => 'datetime',
            'validated_at' => 'datetime',
            'prioritas' => PrioritasOrder::class,
            'status' => StatusOrderLab::class,
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
        return $this->hasMany(OrderLabDetail::class, 'order_id');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(HasilLab::class, 'order_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public static function generateNoOrder(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "LAB/{$year}/{$month}";

        $last = self::where('no_order', 'like', "{$prefix}/%")
            ->orderByDesc('no_order')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->no_order, -5)) + 1 : 1;

        return sprintf('%s/%05d', $prefix, $seq);
    }
}
