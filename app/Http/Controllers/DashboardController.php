<?php

namespace App\Http\Controllers;

use App\Enums\StatusKamar;
use App\Enums\StatusKunjungan;
use App\Models\Kamar;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\StokObat;
use App\Models\Tagihan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $stats = [
            'total_pasien' => Pasien::count(),
            'pasien_baru_hari_ini' => Pasien::whereDate('created_at', $today)->count(),
            'kunjungan_hari_ini' => Kunjungan::whereDate('tgl_masuk', $today)->count(),
            'rj_aktif' => Kunjungan::tipeRJ()->aktif()->whereDate('tgl_masuk', $today)->count(),
            'ri_aktif' => Kunjungan::tipeRI()->aktif()->count(),
            'igd_aktif' => Kunjungan::tipeIGD()->aktif()->whereDate('tgl_masuk', $today)->count(),
            'kamar_tersedia' => Kamar::where('status', StatusKamar::Tersedia)->where('is_active', true)->count(),
            'kamar_total' => Kamar::where('is_active', true)->count(),
            'pendapatan_hari_ini' => Tagihan::whereDate('tgl_tagihan', $today)
                ->where('status', '!=', 'VOID')
                ->sum('dibayar'),
        ];

        // Alerts
        $obatHampirHabis = Obat::with('stok')
            ->where('is_active', true)
            ->get()
            ->filter(fn($o) => $o->is_kurang_stok)
            ->take(10);

        $obatHampirExp = StokObat::willExpireSoon(90)
            ->with('obat')
            ->orderBy('exp_date')
            ->limit(10)
            ->get();

        $antrianHariIni = Kunjungan::with(['pasien', 'rawatJalan.poli'])
            ->whereDate('tgl_masuk', $today)
            ->where('status', StatusKunjungan::Terdaftar)
            ->latest('tgl_masuk')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'obatHampirHabis',
            'obatHampirExp',
            'antrianHariIni'
        ));
    }
}
