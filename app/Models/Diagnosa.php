<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosa extends Model
{
    use HasUuid;

    protected $table = 'diagnosa';

    protected $fillable = [
        'kunjungan_id',
        'icd10_kode',
        'tipe',
        'catatan',
        'dokter_id',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function icd10(): BelongsTo
    {
        return $this->belongsTo(Icd10::class, 'icd10_kode', 'kode');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }
}
