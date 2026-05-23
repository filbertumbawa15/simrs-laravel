<?php

namespace App\Models;

use App\Enums\AlasanRujuk;
use App\Enums\TransportasiRujuk;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RujukanKeluar extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'rujukan_keluar';

    protected $fillable = [
        'kunjungan_id',
        'rs_tujuan',
        'alasan',
        'catatan',
        'transportasi',
        'tgl_rujuk',
        'dirujuk_oleh',
        'jawaban_rs_tujuan',
        'jawaban_at',
    ];

    protected $casts = [
        'alasan' => AlasanRujuk::class,
        'transportasi' => TransportasiRujuk::class,
        'tgl_rujuk' => 'datetime',
        'jawaban_at' => 'datetime',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function dirujukOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dirujuk_oleh');
    }
}
