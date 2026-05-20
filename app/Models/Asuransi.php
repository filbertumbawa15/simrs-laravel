<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asuransi extends Model
{
    use HasUuid;

    protected $table = 'asuransi';

    protected $fillable = ['kode', 'nama', 'tipe', 'kontak', 'alamat', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function pasien(): HasMany
    {
        return $this->hasMany(AsuransiPasien::class);
    }
}
