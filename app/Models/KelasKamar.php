<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasKamar extends Model
{
    use HasUuid;

    protected $table = 'kelas_kamar';

    protected $fillable = ['kode', 'nama', 'tarif_per_hari', 'urutan'];

    protected function casts(): array
    {
        return ['tarif_per_hari' => 'decimal:2'];
    }

    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class, 'kelas_id');
    }
}
