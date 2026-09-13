<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasienRequest;
use App\Models\Pasien;
use App\Services\PendaftaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasienController extends Controller
{
    /** Whitelist kolom yang bisa di-sort dari client. */
    private const SORTABLE = ['no_rm', 'nama', 'nik', 'jenis_kelamin', 'tgl_lahir', 'telp', 'created_at'];

    public function __construct(protected PendaftaranService $pendaftaran) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pasien::class);

        // Halaman shell — data di-fetch via AJAX ke pasien.data
        return view('pasien.index');
    }

    /**
     * JSON endpoint untuk table (dipakai Alpine dataTable component).
     */
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pasien::class);

        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 15)));

        $query = Pasien::query()->search($request->input('q'));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest();
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn ($p) => [
                'id' => $p->id,
                'no_rm' => $p->no_rm,
                'nama' => $p->nama,
                'nik' => $p->nik,
                'jenis_kelamin' => $p->jenis_kelamin?->value,
                'jenis_kelamin_label' => $p->jenis_kelamin?->label(),
                'tgl_lahir' => $p->tgl_lahir?->format('d M Y'),
                'tempat_lahir' => $p->tempat_lahir,
                'umur' => $p->umur,
                'telp' => $p->telp,
                'alamat' => $p->alamat,
                'urls' => [
                    'show' => route('pasien.show', $p),
                    'buat_kunjungan' => route('kunjungan.create', ['pasien_id' => $p->id]),
                ],
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Pasien::class);

        return view('pasien.create');
    }

    public function store(StorePasienRequest $request): RedirectResponse
    {
        $pasien = $this->pendaftaran->daftarPasienBaru($request->validated());

        return redirect()
            ->route('pasien.show', $pasien)
            ->with('success', "Pasien {$pasien->nama} terdaftar dengan No. RM {$pasien->no_rm}.");
    }

    public function show(Pasien $pasien): View
    {
        $this->authorize('view', $pasien);

        $pasien->load(['rekamMedis', 'asuransi.asuransi', 'kunjungan' => function ($q) {
            $q->latest('tgl_masuk')->limit(10);
        }]);

        return view('pasien.show', compact('pasien'));
    }

    public function edit(Pasien $pasien): View
    {
        $this->authorize('update', $pasien);

        return view('pasien.edit', compact('pasien'));
    }

    public function update(StorePasienRequest $request, Pasien $pasien): RedirectResponse
    {
        $pasien->update($request->validated());

        return redirect()
            ->route('pasien.show', $pasien)
            ->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(Pasien $pasien): RedirectResponse
    {
        $this->authorize('delete', $pasien);

        // Soft delete. Production: cek dulu apakah pasien punya kunjungan aktif.
        if ($pasien->kunjungan()->aktif()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus pasien yang masih punya kunjungan aktif.');
        }

        $pasien->delete();

        return redirect()
            ->route('pasien.index')
            ->with('success', 'Data pasien diarsipkan.');
    }
}
