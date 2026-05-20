<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poli extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'poli';

    protected $fillable = ['kode', 'nama', 'lokasi', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalDokter::class);
    }

    public function rawatJalan(): HasMany
    {
        return $this->hasMany(RawatJalan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
