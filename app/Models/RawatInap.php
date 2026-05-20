<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawatInap extends Model
{
    use HasUuid;

    protected $table = 'rawat_inap';

    protected $fillable = [
        'kunjungan_id',
        'dpjp_id',
        'tgl_masuk_ri',
        'tgl_pulang',
        'cara_pulang',
        'alasan_masuk',
        'resume_medis',
        'instruksi_pulang',
        'resume_finalized',
        'resume_finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'tgl_masuk_ri' => 'datetime',
            'tgl_pulang' => 'datetime',
            'resume_finalized' => 'boolean',
            'resume_finalized_at' => 'datetime',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function dpjp(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dpjp_id');
    }

    public function kamarInap(): HasMany
    {
        return $this->hasMany(KamarInap::class)->orderBy('masuk');
    }

    public function kamarAktif(): HasOne
    {
        return $this->hasOne(KamarInap::class)->whereNull('keluar')->latestOfMany('masuk');
    }

    public function getLamaInapAttribute(): int
    {
        $end = $this->tgl_pulang ?: now();

        return max(1, $this->tgl_masuk_ri->diffInDays($end));
    }
}
