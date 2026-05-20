<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLabDetail extends Model
{
    use HasUuid;

    protected $table = 'order_lab_detail';

    protected $fillable = ['order_id', 'parameter_id', 'tarif'];

    protected function casts(): array
    {
        return ['tarif' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderLab::class, 'order_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(ParameterLab::class, 'parameter_id');
    }
}
