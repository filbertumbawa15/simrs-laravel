<?php

namespace App\Http\Controllers;

use App\Models\OrderLab;
use App\Models\Pembayaran;
use App\Models\RawatInap;
use App\Models\Resep;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Endpoint publik untuk verifikasi dokumen PDF via QR scan.
 * Show minimal info (sah/tidak, jenis dokumen, tanggal, ref) — TIDAK expose PHI.
 *
 * URL pattern: /verify/{docType}/{docId}
 * docType: kuitansi | resep | resume | hasil-lab
 *
 * TIDAK di-guard auth — publik untuk pasien/eksternal cek keabsahan.
 */
class VerifyController extends Controller
{
    public function show(string $docType, string $docId): View|Response
    {
        $data = match ($docType) {
            'kuitansi' => $this->verifyKuitansi($docId),
            'resep' => $this->verifyResep($docId),
            'resume' => $this->verifyResume($docId),
            'hasil-lab' => $this->verifyHasilLab($docId),
            default => null,
        };

        if (! $data) {
            return response()->view('verify.not-found', [], 404);
        }

        return response()->view('verify.show', $data);
    }

    protected function verifyKuitansi(string $id): ?array
    {
        $p = Pembayaran::find($id);
        if (! $p) return null;

        return [
            'valid' => ! $p->is_void,
            'title' => 'Kuitansi Pembayaran',
            'no' => $p->no_pembayaran,
            'tanggal' => $p->tgl_bayar->format('d M Y H:i'),
            'ref' => 'Rp ' . number_format($p->jumlah, 0, ',', '.'),
            'status' => $p->is_void ? 'DIVOID' : 'SAH',
        ];
    }

    protected function verifyResep(string $id): ?array
    {
        $r = Resep::find($id);
        if (! $r) return null;

        return [
            'valid' => in_array($r->status, ['DIVERIFIKASI', 'DISERAHKAN']),
            'title' => 'Resep',
            'no' => $r->no_resep,
            'tanggal' => $r->tgl_resep->format('d M Y'),
            'ref' => $r->status,
            'status' => $r->status,
        ];
    }

    protected function verifyResume(string $id): ?array
    {
        $ri = RawatInap::find($id);
        if (! $ri || ! $ri->resume_finalized) return null;

        return [
            'valid' => true,
            'title' => 'Resume Medis Rawat Inap',
            'no' => 'RI-' . substr($ri->id, 0, 8),
            'tanggal' => $ri->resume_finalized_at?->format('d M Y H:i'),
            'ref' => $ri->cara_pulang?->value ?? '-',
            'status' => 'FINAL',
        ];
    }

    protected function verifyHasilLab(string $id): ?array
    {
        $o = OrderLab::find($id);
        if (! $o) return null;

        return [
            'valid' => $o->status->value === 'SELESAI',
            'title' => 'Hasil Laboratorium',
            'no' => $o->no_order,
            'tanggal' => $o->validated_at?->format('d M Y H:i'),
            'ref' => $o->status->value,
            'status' => $o->status->value === 'SELESAI' ? 'DIVALIDASI' : $o->status->value,
        ];
    }
}
