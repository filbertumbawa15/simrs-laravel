<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Icd10 extends Model
{
    protected $table = 'icd10';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['kode', 'nama', 'kategori', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function diagnosa(): HasMany
    {
        return $this->hasMany(Diagnosa::class, 'icd10_kode', 'kode');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('kode', 'like', "{$term}%")
                ->orWhere('nama', 'like', "%{$term}%");
        });
    }
}