<?php

namespace App\Http\Controllers;

use App\Enums\StatusKamar;
use App\Models\Dokter;
use App\Models\Kamar;
use App\Models\KelasKamar;
use App\Models\Kunjungan;
use App\Models\RawatInap;
use App\Services\RawatInapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawatInapController extends Controller
{
    private const SORTABLE = ['tgl_masuk_ri', 'tgl_pulang', 'cara_pulang'];

    public function __construct(protected RawatInapService $service) {}

    /**
     * Daftar pasien rawat inap aktif.
     */
    public function index(): View
    {
        return view('ri.index');
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'aktif');

        $query = RawatInap::query()
            ->with(['kunjungan.pasien', 'dpjp', 'kamarAktif.kamar.kelas'])
            ->when($status === 'aktif', fn ($qq) => $qq->whereNull('tgl_pulang'))
            ->when($status === 'pulang', fn ($qq) => $qq->whereNotNull('tgl_pulang'))
            ->when($q !== '', fn ($qq) => $qq->whereHas('kunjungan.pasien', fn($p) =>
                $p->where('nama', 'like', "%{$q}%")->orWhere('no_rm', 'like', "%{$q}%")
            ));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest('tgl_masuk_ri');
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($ri) => [
                'id' => $ri->id,
                'tgl_masuk_ri' => $ri->tgl_masuk_ri?->format('d M Y H:i'),
                'tgl_pulang' => $ri->tgl_pulang?->format('d M Y H:i'),
                'pasien' => [
                    'nama' => $ri->kunjungan->pasien->nama,
                    'no_rm' => $ri->kunjungan->pasien->no_rm,
                    'umur' => $ri->kunjungan->pasien->umur,
                ],
                'kamar' => $ri->kamarAktif?->kamar?->no_kamar,
                'kelas' => $ri->kamarAktif?->kamar?->kelas?->nama,
                'dpjp' => $ri->dpjp->nama_lengkap,
                'cara_pulang' => $ri->cara_pulang,
                'url' => route('ri.show', $ri),
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
     * Form admisi.
     */
    public function admisiForm(Request $request): View
    {
        $kunjungan = Kunjungan::with('pasien')
            ->findOrFail($request->input('kunjungan_id'));

        // Group kamar tersedia per kelas
        $kamarTersedia = Kamar::tersedia()
            ->with('kelas')
            ->get()
            ->groupBy('kelas.nama');

        $dokter = Dokter::active()->orderBy('nama')->get();

        return view('ri.admisi', compact('kunjungan', 'kamarTersedia', 'dokter'));
    }

    public function admisiStore(Request $request)
    {
        $data = $request->validate([
            'kunjungan_id' => ['required', 'uuid', 'exists:kunjungan,id'],
            'kamar_id' => ['required', 'uuid', 'exists:kamar,id'],
            'dpjp_id' => ['required', 'uuid', 'exists:dokter,id'],
            'alasan_masuk' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $kunjungan = Kunjungan::findOrFail($data['kunjungan_id']);
            $ri = $this->service->admisi(
                kunjungan: $kunjungan,
                kamarId: $data['kamar_id'],
                dpjpId: $data['dpjp_id'],
                alasanMasuk: $data['alasan_masuk'],
            );

            return redirect()
                ->route('ri.show', $ri)
                ->with('success', 'Admisi berhasil. Pasien ditempatkan di kamar.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(RawatInap $ri): View
    {
        $ri->load([
            'kunjungan.pasien.rekamMedis',
            'dpjp',
            'kamarInap.kamar.kelas',
            'kunjungan.diagnosa.icd10',
            'kunjungan.tindakan.tindakan',
            'kunjungan.cppt.user',
        ]);

        return view('ri.show', compact('ri'));
    }

    /**
     * Form pindah kamar.
     */
    public function pindahForm(RawatInap $ri): View
    {
        $kamarTersedia = Kamar::tersedia()
            ->with('kelas')
            ->get()
            ->groupBy('kelas.nama');

        return view('ri.pindah', compact('ri', 'kamarTersedia'));
    }

    public function pindahStore(Request $request, RawatInap $ri)
    {
        $data = $request->validate([
            'kamar_baru_id' => ['required', 'uuid', 'exists:kamar,id'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->service->pindahKamar($ri, $data['kamar_baru_id'], $data['alasan'] ?? null);

            return redirect()->route('ri.show', $ri)
                ->with('success', 'Pasien dipindahkan ke kamar baru.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form pulang.
     */
    public function pulangForm(RawatInap $ri): View
    {
        if ($ri->tgl_pulang) {
            return redirect()->route('ri.show', $ri)->with('error', 'Pasien sudah pulang.');
        }

        return view('ri.pulang', compact('ri'));
    }

    public function pulangStore(Request $request, RawatInap $ri)
    {
        $data = $request->validate([
            'cara_pulang' => ['required', 'in:SEMBUH,MEMBAIK,BELUM_SEMBUH,APS,RUJUK,MENINGGAL'],
            'resume_medis' => ['required', 'string', 'min:50'],
            'instruksi_pulang' => ['nullable', 'string'],
        ]);

        try {
            $this->service->pulang(
                ri: $ri,
                caraPulang: $data['cara_pulang'],
                resumeMedis: $data['resume_medis'],
                instruksiPulang: $data['instruksi_pulang'] ?? null,
            );

            return redirect()->route('ri.show', $ri)
                ->with('success', 'Pasien berhasil dipulangkan. Kunjungan menunggu pembayaran.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
