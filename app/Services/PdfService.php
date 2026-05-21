<?php

namespace App\Services;

use App\Models\HasilLab;
use App\Models\Pembayaran;
use App\Models\RawatInap;
use App\Models\Resep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    public function resep(Resep $resep): Response
    {
        $resep->load(['kunjungan.pasien', 'dokter', 'details.obat']);
        return Pdf::loadView('pdf.resep', compact('resep'))
            ->setPaper('a5', 'portrait')
            ->stream("Resep-{$resep->no_resep}.pdf");
    }

    public function kuitansi(Pembayaran $pembayaran): Response
    {
        $pembayaran->load(['tagihan.kunjungan.pasien', 'tagihan.details', 'kasir']);
        return Pdf::loadView('pdf.kuitansi', compact('pembayaran'))
            ->setPaper('a5', 'portrait')
            ->stream("Kuitansi-{$pembayaran->no_pembayaran}.pdf");
    }

    public function resumeMedis(RawatInap $ri): Response
    {
        $ri->load([
            'kunjungan.pasien', 'dpjp', 'kamarInap.kamar.kelas',
            'kunjungan.diagnosa.icd10',
        ]);
        if (! $ri->resume_finalized) {
            abort(400, 'Resume medis belum difinalisasi.');
        }
        return Pdf::loadView('pdf.resume-medis', compact('ri'))
            ->setPaper('a4', 'portrait')
            ->stream("ResumeMedis-{$ri->kunjungan->pasien->no_rm}.pdf");
    }

    public function hasilLab(\App\Models\OrderLab $order): Response
    {
        $order->load(['kunjungan.pasien', 'dokter', 'hasil.parameter', 'validator']);
        if ($order->status->value !== 'SELESAI') {
            abort(400, 'Hasil lab belum divalidasi.');
        }
        return Pdf::loadView('pdf.hasil-lab', compact('order'))
            ->setPaper('a4', 'portrait')
            ->stream("HasilLab-{$order->no_order}.pdf");
    }
}
