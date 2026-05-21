<?php

namespace App\Services;

use App\Enums\PrioritasOrder;
use App\Enums\StatusKunjungan;
use App\Enums\StatusOrderRadiologi;
use App\Models\HasilRadiologi;
use App\Models\HasilRadiologiImage;
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use App\Models\PemeriksaanRadiologi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiologiService
{
    /**
     * Dokter buat order pemeriksaan radiologi.
     * Snapshot tarif saat order.
     */
    public function buatOrder(
        string $kunjunganId,
        string $dokterId,
        array $pemeriksaanIds,
        PrioritasOrder $prioritas = PrioritasOrder::Rutin,
        ?string $klinis = null,
        ?string $diagnosaKerja = null,
        bool $hamil = false,
        bool $persiapanPuasa = false,
    ): OrderRadiologi {
        if (empty($pemeriksaanIds)) {
            throw new \InvalidArgumentException('Order harus berisi minimal 1 pemeriksaan.');
        }

        return DB::transaction(function () use (
            $kunjunganId, $dokterId, $pemeriksaanIds, $prioritas,
            $klinis, $diagnosaKerja, $hamil, $persiapanPuasa
        ) {
            $order = OrderRadiologi::create([
                'no_order' => OrderRadiologi::generateNoOrder(),
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'tgl_order' => now(),
                'prioritas' => $prioritas,
                'status' => StatusOrderRadiologi::Diorder,
                'klinis' => $klinis,
                'diagnosa_kerja' => $diagnosaKerja,
                'hamil' => $hamil,
                'persiapan_puasa' => $persiapanPuasa,
            ]);

            $pemeriksaans = PemeriksaanRadiologi::whereIn('id', $pemeriksaanIds)->get();
            foreach ($pemeriksaans as $p) {
                OrderRadiologiDetail::create([
                    'order_id' => $order->id,
                    'pemeriksaan_id' => $p->id,
                    'tarif' => $p->tarif_kelas3, // default kelas 3, billing yang adjust per kelas
                ]);
            }

            return $order->load('details.pemeriksaan');
        });
    }

    /**
     * Radiografer eksekusi: ambil foto, isi kondisi teknis, upload images.
     *
     * @param  UploadedFile[]  $files
     */
    public function eksekusiPemeriksaan(
        OrderRadiologi $order,
        string $radiograferId,
        ?string $kondisiTeknis,
        array $files = [],
    ): OrderRadiologi {
        return DB::transaction(function () use ($order, $radiograferId, $kondisiTeknis, $files) {
            if (! in_array($order->status, [StatusOrderRadiologi::Diorder, StatusOrderRadiologi::Dilakukan])) {
                throw new \DomainException("Order ini tidak bisa dieksekusi (status: {$order->status->label()}).");
            }

            $order->update([
                'radiografer_id' => $radiograferId,
                'eksekusi_at' => $order->eksekusi_at ?: now(),
                'kondisi_teknis' => $kondisiTeknis,
                'status' => StatusOrderRadiologi::Dilakukan,
            ]);

            // Upload images
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }
                $this->uploadImage($order, $file, $radiograferId);
            }

            // Auto-transisi ke MenungguBacaan kalau sudah ada gambar
            if ($order->fresh()->images()->exists()) {
                $order->update(['status' => StatusOrderRadiologi::MenungguBacaan]);
            }

            return $order->fresh(['images', 'details.pemeriksaan']);
        });
    }

    /**
     * Upload satu image. Disk private (rad_images).
     */
    public function uploadImage(OrderRadiologi $order, UploadedFile $file, string $userId, ?string $label = null): HasilRadiologiImage
    {
        $path = $file->store("orders/{$order->id}", 'rad_images');

        return HasilRadiologiImage::create([
            'order_id' => $order->id,
            'disk' => 'rad_images',
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'label' => $label,
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Hapus image (sebelum validasi).
     */
    public function hapusImage(HasilRadiologiImage $image): void
    {
        if ($image->order->status === StatusOrderRadiologi::Selesai) {
            throw new \DomainException('Tidak bisa hapus image setelah order selesai/divalidasi.');
        }
        $image->delete(); // file auto-deleted via model booted
    }

    /**
     * Radiolog input bacaan untuk SATU pemeriksaan.
     */
    public function inputBacaan(
        OrderRadiologi $order,
        string $pemeriksaanId,
        string $radiologId,
        ?string $bacaan,
        ?string $kesan,
        ?string $saran,
        bool $adaTemuanKritis = false,
    ): HasilRadiologi {
        return DB::transaction(function () use ($order, $pemeriksaanId, $radiologId, $bacaan, $kesan, $saran, $adaTemuanKritis) {
            return HasilRadiologi::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'pemeriksaan_id' => $pemeriksaanId,
                ],
                [
                    'bacaan' => $bacaan,
                    'kesan' => $kesan,
                    'saran' => $saran,
                    'ada_temuan_kritis' => $adaTemuanKritis,
                    'radiolog_id' => $radiologId,
                ]
            );
        });
    }

    /**
     * Finalize: radiolog sign-off semua hasil di order.
     * Setelah ini tidak bisa edit & DPJP bisa lihat.
     */
    public function validasi(OrderRadiologi $order, string $radiologId): OrderRadiologi
    {
        return DB::transaction(function () use ($order, $radiologId) {
            // Pastikan semua pemeriksaan sudah ada bacaan
            $jumlahDetail = $order->details()->count();
            $jumlahHasil = $order->hasil()->count();

            if ($jumlahHasil < $jumlahDetail) {
                throw new \DomainException(
                    "Belum semua pemeriksaan dibaca ({$jumlahHasil}/{$jumlahDetail}). Lengkapi dulu."
                );
            }

            if (! $order->images()->exists()) {
                throw new \DomainException('Tidak ada image. Validasi tidak bisa tanpa bukti citra.');
            }

            $order->hasil()->update([
                'finalized_at' => now(),
                'radiolog_id' => $radiologId,
            ]);

            $order->update([
                'status' => StatusOrderRadiologi::Selesai,
                'radiolog_id' => $radiologId,
                'validated_at' => now(),
            ]);

            // Notifikasi temuan kritis
            $this->notifyKritis($order);

            // Auto-advance status kunjungan kalau semua order radiologi & lab selesai
            $kunjungan = $order->kunjungan;
            $adaRadBelum = $kunjungan->orderRadiologi()
                ->whereNotIn('status', [StatusOrderRadiologi::Selesai, StatusOrderRadiologi::Batal])
                ->exists();

            $adaLabBelum = $kunjungan->orderLab()
                ->whereNotIn('status', ['SELESAI', 'BATAL'])
                ->exists();

            if (! $adaRadBelum && ! $adaLabBelum && $kunjungan->status === StatusKunjungan::MenungguHasilLab) {
                $kunjungan->update(['status' => StatusKunjungan::MenungguPembayaran]);
            }

            return $order->fresh(['hasil.pemeriksaan', 'radiolog', 'images']);
        });
    }

    protected function notifyKritis(OrderRadiologi $order): void
    {
        $kritis = $order->hasil()->where('ada_temuan_kritis', true)->get();
        foreach ($kritis as $h) {
            if ($h->critical_notified) continue;

            $h->update([
                'critical_notified' => true,
                'critical_notified_at' => now(),
            ]);

            Log::channel('audit')->warning('Temuan kritis radiologi', [
                'order' => $order->no_order,
                'pasien' => $order->kunjungan->pasien->nama,
                'no_rm' => $order->kunjungan->pasien->no_rm,
                'pemeriksaan' => $h->pemeriksaan->nama,
                'kesan' => $h->kesan,
                'dpjp' => $order->dokter->nama_lengkap,
            ]);

            // TODO: dispatch WA/SMS job
        }
    }
}
