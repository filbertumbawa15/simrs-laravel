<?php

namespace App\Models;

use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Kunjungan extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $table = 'kunjungan';

    protected $fillable = [
        'no_kunjungan',
        'pasien_id',
        'tipe',
        'tgl_masuk',
        'tgl_keluar',
        'status',
        'penjamin',
        'asuransi_pasien_id',
        'no_rujukan',
        'no_sep',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => TipeKunjungan::class,
            'penjamin' => Penjamin::class,
            'status' => StatusKunjungan::class,
            'tgl_masuk' => 'datetime',
            'tgl_keluar' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'tgl_keluar', 'penjamin'])
            ->logOnlyDirty();
    }

    // ============================
    // Relationships
    // ============================
    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function asuransiPasien(): BelongsTo
    {
        return $this->belongsTo(AsuransiPasien::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rawatJalan(): HasOne
    {
        return $this->hasOne(RawatJalan::class);
    }

    public function rawatInap(): HasOne
    {
        return $this->hasOne(RawatInap::class);
    }

    public function triase(): HasOne
    {
        return $this->hasOne(TriaseIgd::class);
    }

    public function diagnosa(): HasMany
    {
        return $this->hasMany(Diagnosa::class);
    }

    public function diagnosaPrimer(): HasOne
    {
        return $this->hasOne(Diagnosa::class)->where('tipe', 'PRIMER');
    }

    public function tindakan(): HasMany
    {
        return $this->hasMany(TindakanKunjungan::class);
    }

    public function orderLab(): HasMany
    {
        return $this->hasMany(OrderLab::class);
    }

    public function resep(): HasMany
    {
        return $this->hasMany(Resep::class);
    }

    public function cppt(): HasMany
    {
        return $this->hasMany(Cppt::class)->orderBy('waktu_catatan');
    }

    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class);
    }

    // ============================
    // Helpers
    // ============================
    public static function generateNoKunjungan(TipeKunjungan $tipe): string
    {
        $prefix = $tipe->value;
        $year = now()->format('Y');
        $month = now()->format('m');

        $last = self::where('no_kunjungan', 'like', "{$prefix}/{$year}/{$month}/%")
            ->orderByDesc('no_kunjungan')
            ->lockForUpdate()
            ->first();

        $seq = $last
            ? ((int) substr($last->no_kunjungan, -5)) + 1
            : 1;

        return sprintf('%s/%s/%s/%05d', $prefix, $year, $month, $seq);
    }

    // ============================
    // Scopes
    // ============================
    public function scopeAktif($query)
    {
        return $query->whereNotIn('status', [
            StatusKunjungan::Selesai,
            StatusKunjungan::Batal,
        ]);
    }

    public function scopeTipeRJ($query)
    {
        return $query->where('tipe', TipeKunjungan::RawatJalan);
    }

    public function scopeTipeRI($query)
    {
        return $query->where('tipe', TipeKunjungan::RawatInap);
    }

    public function scopeTipeIGD($query)
    {
        return $query->where('tipe', TipeKunjungan::IGD);
    }

    public function orderRadiologi(): HasMany
    {
        return $this->hasMany(OrderRadiologi::class);
    }
}
