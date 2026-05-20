<?php

namespace App\Models;

use App\Enums\FlagHasilLab;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilLab extends Model
{
    use HasUuid;

    protected $table = 'hasil_lab';

    protected $fillable = [
        'order_id',
        'parameter_id',
        'hasil',
        'hasil_numerik',
        'satuan',
        'nilai_rujukan',
        'flag',
        'catatan',
        'critical_notified',
        'critical_notified_at',
        'input_oleh',
        'validator_id',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'hasil_numerik' => 'decimal:4',
            'flag' => FlagHasilLab::class,
            'critical_notified' => 'boolean',
            'critical_notified_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderLab::class, 'order_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(ParameterLab::class, 'parameter_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_oleh');
    }
}
