<?php

namespace App\Services;

use App\Models\HasilLab;
use App\Models\OrderLab;
use App\Models\Pembayaran;
use App\Models\RawatInap;
use App\Models\RawatJalan;
use App\Models\Resep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    public function __construct(protected QrCodeService $qr)
    {
    }

    public function resep(Resep $resep, bool $isSalinan = false): Response
    {
        $resep->load(['kunjungan.pasien', 'dokter', 'details.obat']);

        return Pdf::loadView('pdf.resep', $this->withHeader('resep', $resep->id, [
            'resep' => $resep,
            'docNumber' => $resep->no_resep,
            'isSalinan' => $isSalinan,
        ]))
            ->setPaper('a5', 'portrait')
            ->stream($this->safeFilename("Resep-{$resep->no_resep}.pdf"));
    }

    public function kuitansi(Pembayaran $pembayaran, bool $isSalinan = false): Response
    {
        $pembayaran->load(['tagihan.kunjungan.pasien', 'tagihan.details', 'kasir']);

        return Pdf::loadView('pdf.kuitansi', $this->withHeader('kuitansi', $pembayaran->id, [
            'pembayaran' => $pembayaran,
            'docNumber' => $pembayaran->no_pembayaran,
            'isSalinan' => $isSalinan,
        ]))
            ->setPaper('a5', 'portrait')
            ->stream($this->safeFilename("Kuitansi-{$pembayaran->no_pembayaran}.pdf"));
    }

    public function resumeMedis(RawatInap $ri, bool $isSalinan = false): Response
    {
        $ri->load([
            'kunjungan.pasien', 'dpjp', 'kamarInap.kamar.kelas',
            'kunjungan.diagnosa.icd10',
        ]);

        if (! $ri->resume_finalized) {
            abort(400, 'Resume medis belum difinalisasi.');
        }

        return Pdf::loadView('pdf.resume-medis', $this->withHeader('resume', $ri->id, [
            'ri' => $ri,
            'docNumber' => 'RM ' . $ri->kunjungan->pasien->no_rm,
            'isSalinan' => $isSalinan,
        ]))
            ->setPaper('a4', 'portrait')
            ->stream($this->safeFilename("ResumeMedis-{$ri->kunjungan->pasien->no_rm}.pdf"));
    }

    public function hasilLab(OrderLab $order, bool $isSalinan = false): Response
    {
        $order->load(['kunjungan.pasien', 'dokter', 'hasil.parameter', 'validator']);

        if ($order->status->value !== 'SELESAI') {
            abort(400, 'Hasil lab belum divalidasi.');
        }

        return Pdf::loadView('pdf.hasil-lab', $this->withHeader('hasil-lab', $order->id, [
            'order' => $order,
            'docNumber' => $order->no_order,
            'isSalinan' => $isSalinan,
        ]))
            ->setPaper('a4', 'portrait')
            ->stream($this->safeFilename("HasilLab-{$order->no_order}.pdf"));
    }

    /**
     * Cetak tiket antrian ke thermal printer 58mm.
     * Paper size: 58mm x 100mm (dinamis, cukup untuk QR + info).
     */
    public function tiketAntrian(RawatJalan $rj): Response
    {
        $rj->load(['kunjungan.pasien', 'poli', 'dokter']);

        // QR isi: url ke halaman antrian (untuk display board / status update)
        $qrContent = url("/rj/antrian?poli_id={$rj->poli_id}");
        $qrDataUri = $this->qr->svgDataUri($qrContent, 100);

        return Pdf::loadView('pdf.tiket-antrian', [
            'rj' => $rj,
            'qrDataUri' => $qrDataUri,
        ])
            // 58mm = 164.4pt lebar, 100mm = 283.5pt tinggi
            ->setPaper([0, 0, 164.4, 283.5])
            ->stream($this->safeFilename("Tiket-{$rj->no_antrian}.pdf"));
    }

    /**
     * Inject QR verifikasi + verify URL ke data blade.
     * Semua PDF template gunakan variable ini via layout.
     */
    protected function withHeader(string $docType, string $docId, array $data): array
    {
        $verifyUrl = $this->qr->verifyUrl($docType, $docId);
        $qrDataUri = $this->qr->svgDataUri($verifyUrl);

        return array_merge($data, [
            'verifyUrl' => $verifyUrl,
            'qrDataUri' => $qrDataUri,
        ]);
    }

    /**
     * Ganti karakter yang tidak valid di HTTP Content-Disposition filename.
     * SIHRS pakai nomor dokumen dengan "/" (RX/2026/09/00001) — perlu di-replace.
     */
    protected function safeFilename(string $name): string
    {
        return str_replace(['/', '\\'], '-', $name);
    }
}
