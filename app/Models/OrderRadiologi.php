<?php

namespace App\Models;

use App\Enums\PrioritasOrder;
use App\Enums\StatusOrderRadiologi;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderRadiologi extends Model
{
    use HasUuid;

    protected $table = 'order_radiologi';

    protected $fillable = [
        'no_order', 'kunjungan_id', 'dokter_id', 'tgl_order',
        'prioritas', 'status', 'klinis', 'diagnosa_kerja',
        'hamil', 'persiapan_puasa',
        'radiografer_id', 'eksekusi_at', 'kondisi_teknis',
        'radiolog_id', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'tgl_order' => 'datetime',
            'eksekusi_at' => 'datetime',
            'validated_at' => 'datetime',
            'prioritas' => PrioritasOrder::class,
            'status' => StatusOrderRadiologi::class,
            'hamil' => 'boolean',
            'persiapan_puasa' => 'boolean',
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
        return $this->hasMany(OrderRadiologiDetail::class, 'order_id');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(HasilRadiologi::class, 'order_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HasilRadiologiImage::class, 'order_id');
    }

    public function radiografer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radiografer_id');
    }

    public function radiolog(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radiolog_id');
    }

    public static function generateNoOrder(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "RAD/{$year}/{$month}";

        $last = self::where('no_order', 'like', "{$prefix}/%")
            ->orderByDesc('no_order')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->no_order, -5)) + 1 : 1;

        return sprintf('%s/%05d', $prefix, $seq);
    }
}
