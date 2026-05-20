<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlaimBpjs extends Model
{
    use HasUuid;

    protected $table = 'klaim_bpjs';

    protected $fillable = [
        'tagihan_id',
        'kunjungan_id',
        'no_sep',
        'inacbg_kode',
        'inacbg_deskripsi',
        'tarif_inacbg',
        'tarif_rs',
        'selisih',
        'status',
        'diajukan_at',
        'dibayar_at',
        'catatan_verifikator',
    ];

    protected function casts(): array
    {
        return [
            'tarif_inacbg' => 'decimal:2',
            'tarif_rs' => 'decimal:2',
            'selisih' => 'decimal:2',
            'diajukan_at' => 'datetime',
            'dibayar_at' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }
}
