<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawatJalan extends Model
{
    use HasUuid;

    protected $table = 'rawat_jalan';

    protected $fillable = [
        'kunjungan_id', 'poli_id', 'dokter_id', 'no_antrian',
        'waktu_panggilan', 'waktu_mulai_periksa', 'waktu_selesai_periksa',
        'subjective', 'objective', 'tanda_vital', 'assessment', 'plan',
        'edukasi', 'rujuk_internal', 'rujuk_eksternal', 'catatan_rujukan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_panggilan' => 'datetime',
            'waktu_mulai_periksa' => 'datetime',
            'waktu_selesai_periksa' => 'datetime',
            'tanda_vital' => 'array',
            'rujuk_internal' => 'boolean',
            'rujuk_eksternal' => 'boolean',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }
}