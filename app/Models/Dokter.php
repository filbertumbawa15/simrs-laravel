<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokter extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'dokter';

    protected $fillable = [
        'kode',
        'sip',
        'nik',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'spesialisasi',
        'telp',
        'email',
        'jasa_konsul',
        'ihs_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jasa_konsul' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalDokter::class);
    }

    public function rawatJalan(): HasMany
    {
        return $this->hasMany(RawatJalan::class);
    }

    public function rawatInapSebagaiDpjp(): HasMany
    {
        return $this->hasMany(RawatInap::class, 'dpjp_id');
    }

    public function getNamaLengkapAttribute(): string
    {
        return trim(($this->gelar_depan ? $this->gelar_depan . ' ' : '') . $this->nama . ($this->gelar_belakang ? ', ' . $this->gelar_belakang : ''));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
