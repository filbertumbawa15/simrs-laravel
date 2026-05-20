<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obat extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'obat';

    protected $fillable = [
        'kode',
        'nama',
        'nama_generik',
        'golongan',
        'bentuk_sediaan',
        'satuan',
        'kekuatan',
        'kode_kfa',
        'harga_jual',
        'stok_minimum',
        'is_fornas',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'decimal:2',
            'is_fornas' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function stok(): HasMany
    {
        return $this->hasMany(StokObat::class)->where('jumlah_sisa', '>', 0);
    }

    public function semuaStok(): HasMany
    {
        return $this->hasMany(StokObat::class);
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiStokObat::class);
    }

    /**
     * Total stok dari semua batch yang masih ada.
     */
    public function getTotalStokAttribute(): int
    {
        return (int) $this->stok()->sum('jumlah_sisa');
    }

    public function getIsKurangStokAttribute(): bool
    {
        return $this->total_stok < $this->stok_minimum;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('nama_generik', 'like', "%{$term}%")
                ->orWhere('kode', 'like', "%{$term}%");
        });
    }
}
