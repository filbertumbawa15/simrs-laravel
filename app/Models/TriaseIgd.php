<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriaseIgd extends Model
{
    use HasUuid;

    protected $table = 'triase_igd';

    protected $fillable = [
        'kunjungan_id', 'kategori', 'waktu_triase', 'triase_oleh',
        'keluhan_utama', 'tanda_vital',
    ];

    protected function casts(): array
    {
        return [
            'waktu_triase' => 'datetime',
            'tanda_vital' => 'array',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triase_oleh');
    }
}