<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HasilRadiologiImage extends Model
{
    use HasUuid;

    protected $table = 'hasil_radiologi_image';

    protected $fillable = [
        'order_id', 'hasil_id', 'disk', 'path', 'mime',
        'size_bytes', 'label', 'uploaded_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderRadiologi::class, 'order_id');
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilRadiologi::class, 'hasil_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1).' KB';
        return round($bytes / 1048576, 2).' MB';
    }

    /**
     * Hapus file fisik saat record dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $img) {
            Storage::disk($img->disk)->delete($img->path);
        });
    }
}
