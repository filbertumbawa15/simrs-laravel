<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cppt extends Model
{
    use HasUuid;

    protected $table = 'cppt';

    protected $fillable = [
        'kunjungan_id',
        'user_id',
        'profesi',
        'waktu_catatan',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'instruksi',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'waktu_catatan' => 'datetime',
            'verified_by' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
