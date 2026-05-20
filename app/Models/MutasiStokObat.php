<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokObat extends Model
{
    use HasUuid;

    protected $table = 'mutasi_stok_obat';

    protected $fillable = [
        'stok_id',
        'obat_id',
        'jenis',
        'resep_detail_id',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'referensi',
        'user_id',
    ];

    public function stok(): BelongsTo
    {
        return $this->belongsTo(StokObat::class, 'stok_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function resepDetail(): BelongsTo
    {
        return $this->belongsTo(ResepDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
