<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKunjunganRequest;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use App\Services\PendaftaranService;
use App\Services\RawatJalanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KunjunganController extends Controller
{
    private const SORTABLE = ['no_kunjungan', 'tgl_masuk', 'tipe', 'status', 'penjamin'];

    public function __construct(
        protected PendaftaranService $pendaftaran,
        protected RawatJalanService $rj,
    ) {}

    public function index(): View
    {
        return view('kunjungan.index');
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));
        $q = trim((string) $request->input('q', ''));

        $query = Kunjungan::query()
            ->with(['pasien', 'rawatJalan.poli', 'rawatJalan.dokter'])
            ->when($request->input('tipe'), fn($qq, $t) => $qq->where('tipe', $t))
            ->when($request->input('status'), fn($qq, $s) => $qq->where('status', $s))
            ->when($request->input('tanggal'), fn($qq, $d) => $qq->whereDate('tgl_masuk', $d))
            ->when($q !== '', fn($qq) => $qq->where(function ($x) use ($q) {
                $x->where('no_kunjungan', 'like', "%{$q}%")
                  ->orWhereHas('pasien', fn($p) => $p->where('nama', 'like', "%{$q}%")
                      ->orWhere('no_rm', 'like', "%{$q}%")
                      ->orWhere('nik', 'like', "%{$q}%"));
            }));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest('tgl_masuk');
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($k) => [
                'id' => $k->id,
                'no_kunjungan' => $k->no_kunjungan,
                'tipe' => $k->tipe?->value,
                'tipe_label' => $k->tipe?->label(),
                'status' => $k->status?->value,
                'status_label' => $k->status?->label(),
                'penjamin' => $k->penjamin?->value,
                'penjamin_label' => $k->penjamin?->label(),
                'tgl_masuk' => $k->tgl_masuk?->format('d M Y H:i'),
                'pasien' => [
                    'nama' => $k->pasien->nama,
                    'no_rm' => $k->pasien->no_rm,
                    'jenis_kelamin' => $k->pasien->jenis_kelamin?->label(),
                    'umur' => $k->pasien->umur,
                ],
                'poli' => $k->rawatJalan?->poli?->nama,
                'dokter' => $k->rawatJalan?->dokter?->nama_lengkap,
                'url' => route('kunjungan.show', $k),
            ]),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
                'from' => $p->firstItem(),
                'to' => $p->lastItem(),
            ],
        ]);
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
            'triase.petugas',
            'diagnosa.icd10',
            'tindakan.tindakan',
            'orderLab.details.parameter',
            'orderLab.hasil.parameter',
            'orderRadiologi.details.pemeriksaan',
            'orderRadiologi.hasil',
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
