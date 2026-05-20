<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakanKunjungan extends Model
{
    use HasUuid;

    protected $table = 'tindakan_kunjungan';

    protected $fillable = [
        'kunjungan_id',
        'tindakan_id',
        'petugas_id',
        'dokter_id',
        'waktu_tindakan',
        'qty',
        'tarif',
        'subtotal',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_tindakan' => 'datetime',
            'tarif' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function tindakan(): BelongsTo
    {
        return $this->belongsTo(Tindakan::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }
}
