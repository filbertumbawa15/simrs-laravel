<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekamMedis extends Model
{
    use HasUuid;

    protected $table = 'rekam_medis';

    protected $fillable = [
        'pasien_id',
        'riwayat_penyakit',
        'riwayat_keluarga',
        'riwayat_pengobatan',
        'alergi_obat',
        'alergi_makanan',
        'kebiasaan',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }
}
