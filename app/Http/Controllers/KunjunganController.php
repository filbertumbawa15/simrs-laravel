<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKunjunganRequest;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use App\Services\PendaftaranService;
use App\Services\RawatJalanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KunjunganController extends Controller
{
    public function __construct(
        protected PendaftaranService $pendaftaran,
        protected RawatJalanService $rj,
    ) {}

    public function index(Request $request): View
    {
        $kunjungan = Kunjungan::query()
            ->with(['pasien', 'rawatJalan.poli', 'rawatJalan.dokter'])
            ->when($request->input('tipe'), fn($q, $t) => $q->where('tipe', $t))
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('tanggal'), fn($q, $d) => $q->whereDate('tgl_masuk', $d))
            ->latest('tgl_masuk')
            ->paginate(20)
            ->withQueryString();

        return view('kunjungan.index', compact('kunjungan'));
    }

    public function create(Request $request): View
    {
        $pasien = $request->filled('pasien_id')
            ? Pasien::findOrFail($request->input('pasien_id'))
            : null;

        $poli = Poli::active()->orderBy('nama')->get();
        $dokter = Dokter::active()->orderBy('nama')->get();

        return view('kunjungan.create', compact('pasien', 'poli', 'dokter'));
    }

    public function store(StoreKunjunganRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $kunjungan = DB::transaction(function () use ($data) {
            $pasien = Pasien::findOrFail($data['pasien_id']);
            $kunjungan = $this->pendaftaran->buatKunjungan($pasien, $data);

            // Kalau RJ, langsung assign ke poli
            if ($kunjungan->tipe->value === 'RJ' && ! empty($data['poli_id'])) {
                $this->rj->assignKePoli($kunjungan, $data['poli_id'], $data['dokter_id']);
            }

            return $kunjungan;
        });

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', "Kunjungan {$kunjungan->no_kunjungan} berhasil didaftarkan.");
    }

    public function show(Kunjungan $kunjungan): View
    {
        $kunjungan->load([
            'pasien.rekamMedis',
            'rawatJalan.poli',
            'rawatJalan.dokter',
            'rawatInap.dpjp',
            'rawatInap.kamarAktif.kamar.kelas',
            'diagnosa.icd10',
            'tindakan.tindakan',
            'orderLab.details.parameter',
            'orderLab.hasil',
            'resep.details.obat',
            'tagihan.details',
            'cppt.user',
        ]);

        return view('kunjungan.show', compact('kunjungan'));
    }

    public function batal(Kunjungan $kunjungan): RedirectResponse
    {
        $this->authorize('cancel', $kunjungan);

        if ($kunjungan->status->value !== 'TERDAFTAR') {
            return back()->with('error', 'Hanya kunjungan baru terdaftar yang bisa dibatalkan.');
        }

        $kunjungan->update(['status' => 'BATAL']);

        return back()->with('success', 'Kunjungan dibatalkan.');
    }
}
