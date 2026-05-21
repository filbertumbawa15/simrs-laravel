<?php

namespace App\Http\Controllers;

use App\Enums\StatusKamar;
use App\Models\Kamar;
use App\Models\KelasKamar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KamarController extends Controller
{
    /**
     * Bed management board — visual grid semua kamar per kelas dengan warna status.
     */
    public function board(Request $request): View
    {
        $kelas = KelasKamar::with(['kamar' => function ($q) {
            $q->where('is_active', true)
                ->orderBy('no_kamar')
                ->with('kamarInap', fn ($q) => $q->whereNull('keluar')->with('rawatInap.kunjungan.pasien'));
        }])->orderBy('urutan')->get();

        // Statistik
        $stats = [
            'total' => Kamar::where('is_active', true)->count(),
            'tersedia' => Kamar::where('is_active', true)->where('status', StatusKamar::Tersedia)->count(),
            'terisi' => Kamar::where('is_active', true)->where('status', StatusKamar::Terisi)->count(),
            'maintenance' => Kamar::where('is_active', true)->where('status', StatusKamar::Maintenance)->count(),
            'kotor' => Kamar::where('is_active', true)->where('status', StatusKamar::Kotor)->count(),
        ];
        $stats['occupancy_rate'] = $stats['total'] > 0
            ? round(($stats['terisi'] / $stats['total']) * 100, 1)
            : 0;

        return view('ri.board', compact('kelas', 'stats'));
    }

    /**
     * Quick action ubah status kamar (cleaning service, teknisi).
     */
    public function updateStatus(Request $request, Kamar $kamar)
    {
        $data = $request->validate([
            'status' => ['required', 'in:TERSEDIA,MAINTENANCE,KOTOR'],
        ]);

        // Jangan boleh ubah ke TERSEDIA kalau lagi TERISI
        if ($kamar->status === StatusKamar::Terisi) {
            return back()->with('error', 'Kamar sedang terisi, tidak bisa diubah statusnya.');
        }

        $kamar->update(['status' => $data['status']]);

        return back()->with('success', "Status kamar {$kamar->no_kamar} diubah ke {$data['status']}.");
    }
}
