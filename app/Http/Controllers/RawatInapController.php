<?php

namespace App\Http\Controllers;

use App\Enums\StatusKamar;
use App\Models\Dokter;
use App\Models\Kamar;
use App\Models\KelasKamar;
use App\Models\Kunjungan;
use App\Models\RawatInap;
use App\Services\RawatInapService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawatInapController extends Controller
{
    public function __construct(protected RawatInapService $service) {}

    /**
     * Daftar pasien rawat inap aktif.
     */
    public function index(Request $request): View
    {
        $ri = RawatInap::query()
            ->with(['kunjungan.pasien', 'dpjp', 'kamarAktif.kamar.kelas'])
            ->when($request->input('status') === 'aktif', fn ($q) => $q->whereNull('tgl_pulang'))
            ->when($request->input('status') === 'pulang', fn ($q) => $q->whereNotNull('tgl_pulang'))
            ->when(! $request->input('status'), fn ($q) => $q->whereNull('tgl_pulang'))
            ->latest('tgl_masuk_ri')
            ->paginate(20);

        return view('ri.index', compact('ri'));
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
