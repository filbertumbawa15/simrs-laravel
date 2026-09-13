<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Resep;
use App\Services\FarmasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResepController extends Controller
{
    private const SORTABLE = ['no_resep', 'tgl_resep', 'status'];

    public function __construct(protected FarmasiService $service) {}

    /**
     * Daftar resep yang masuk ke farmasi (untuk apoteker).
     */
    public function index(): View
    {
        return view('farmasi.index');
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));
        $q = trim((string) $request->input('q', ''));

        $query = Resep::query()
            ->with(['kunjungan.pasien', 'dokter', 'details.obat'])
            ->when($request->input('status'), fn($qq, $s) => $qq->where('status', $s))
            ->when($q !== '', fn($qq) => $qq->where(function ($x) use ($q) {
                $x->where('no_resep', 'like', "%{$q}%")
                  ->orWhereHas('kunjungan.pasien', fn($p) => $p->where('nama', 'like', "%{$q}%")
                      ->orWhere('no_rm', 'like', "%{$q}%"));
            }));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest('tgl_resep');
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($r) => [
                'id' => $r->id,
                'no_resep' => $r->no_resep,
                'tgl_resep' => $r->tgl_resep?->format('d M Y H:i'),
                'pasien' => [
                    'nama' => $r->kunjungan->pasien->nama,
                    'no_rm' => $r->kunjungan->pasien->no_rm,
                ],
                'dokter' => $r->dokter->nama_lengkap,
                'status' => $r->status,
                'jumlah_item' => $r->details->count(),
                'total' => (float) $r->total,
                'url' => route('resep.show', $r),
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

    /**
     * Form buat resep baru (dipanggil dari halaman periksa RJ).
     */
    public function create(Request $request): View
    {
        $kunjungan = Kunjungan::with('pasien', 'rawatJalan.dokter')
            ->findOrFail($request->input('kunjungan_id'));

        return view('farmasi.create-resep', compact('kunjungan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kunjungan_id' => ['required', 'uuid', 'exists:kunjungan,id'],
            'dokter_id' => ['required', 'uuid', 'exists:dokter,id'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'uuid', 'exists:obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.signa' => ['required', 'string', 'max:50'],
            'items.*.aturan_pakai' => ['nullable', 'string', 'max:100'],
            'items.*.catatan' => ['nullable', 'string'],
        ]);

        $resep = $this->service->buatResep(
            $data['kunjungan_id'],
            $data['dokter_id'],
            $data['items'],
            $data['catatan'] ?? null,
        );

        return redirect()
            ->route('resep.show', $resep)
            ->with('success', "Resep {$resep->no_resep} dibuat.");
    }

    public function show(Resep $resep): View
    {
        $resep->load(['kunjungan.pasien', 'dokter', 'details.obat', 'apoteker', 'penyerah']);

        return view('farmasi.show-resep', compact('resep'));
    }

    /**
     * Apoteker verifikasi resep.
     */
    public function verifikasi(Resep $resep)
    {
        $resep = $this->service->verifikasiResep($resep, auth()->id());

        return back()->with('success', 'Resep diverifikasi.');
    }

    /**
     * Serahkan obat — kurangi stok FEFO.
     */
    public function serahkan(Resep $resep)
    {
        try {
            $this->service->serahkanObat($resep, auth()->id());

            return back()->with('success', 'Obat berhasil diserahkan ke pasien.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX search obat untuk autocomplete saat dokter meresepkan.
     */
    public function searchObat(Request $request)
    {
        return Obat::search($request->input('q'))
            ->where('is_active', true)
            ->with(['stok' => fn($q) => $q->select('obat_id', 'jumlah_sisa')])
            ->limit(20)
            ->get()
            ->map(fn($obat) => [
                'id' => $obat->id,
                'kode' => $obat->kode,
                'nama' => $obat->nama,
                'satuan' => $obat->satuan,
                'kekuatan' => $obat->kekuatan,
                'harga' => (float) $obat->harga_jual,
                'stok' => $obat->total_stok,
            ]);
    }
}
