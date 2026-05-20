<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Models\Concerns\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pasien extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $table = 'pasien';

    protected $fillable = [
        'no_rm',
        'nik',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'agama',
        'pendidikan',
        'pekerjaan',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
        'telp',
        'email',
        'gol_darah',
        'nama_ayah',
        'nama_ibu',
        'kontak_darurat_nama',
        'kontak_darurat_hubungan',
        'kontak_darurat_telp',
        'ihs_id',
        'foto_path',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
            'jenis_kelamin' => JenisKelamin::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ============================
    // Relationships
    // ============================
    public function rekamMedis(): HasOne
    {
        return $this->hasOne(RekamMedis::class);
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }

    public function asuransi(): HasMany
    {
        return $this->hasMany(AsuransiPasien::class);
    }

    // ============================
    // Accessors
    // ============================
    public function getUmurAttribute(): int
    {
        return Carbon::parse($this->tgl_lahir)->age;
    }

    public function getUmurLengkapAttribute(): string
    {
        $birth = Carbon::parse($this->tgl_lahir);
        $now = Carbon::now();
        $diff = $birth->diff($now);

        return "{$diff->y} thn {$diff->m} bln {$diff->d} hr";
    }

    public function getAlamatLengkapAttribute(): string
    {
        return collect([
            $this->alamat,
            $this->rt && $this->rw ? "RT {$this->rt}/RW {$this->rw}" : null,
            $this->kelurahan,
            $this->kecamatan,
            $this->kabupaten,
            $this->provinsi,
            $this->kode_pos,
        ])->filter()->implode(', ');
    }

    // ============================
    // Scopes
    // ============================
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('no_rm', 'like', "%{$term}%")
                ->orWhere('nik', 'like', "%{$term}%")
                ->orWhere('telp', 'like', "%{$term}%");
        });
    }

    // ============================
    // Helpers
    // ============================
    public static function generateNoRm(): string
    {
        $last = self::orderByDesc('no_rm')->lockForUpdate()->first();
        $next = $last ? ((int) $last->no_rm + 1) : 100001;

        return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
