<?php

namespace App\Http\Controllers;

use App\Enums\CaraPulang;
use App\Enums\StatusKamar;
use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Kamar;
use App\Models\KamarInap;
use App\Models\Kunjungan;
use App\Models\RawatInap;
use App\Models\RujukanKeluar;
use App\Services\RawatInapService;
use App\Services\TriaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IgdController extends Controller
{
    public function __construct(
        protected RawatInapService $riService,
        protected TriaseService $triaseService,
    ) {}


    public function board()
    {
        abort_unless(
            auth()->user()->can('igd.view')
                || auth()->user()->hasRole('SUPER_ADMIN'),
            403
        );

        $kunjunganIgd = Kunjungan::query()
            ->where('tipe', TipeKunjungan::IGD)
            ->whereIn('status', [
                StatusKunjungan::Terdaftar,
                StatusKunjungan::DalamPemeriksaan,
                StatusKunjungan::MenungguHasilLab,
            ])
            ->with(['pasien', 'triase.petugas', 'rawatJalan.dokter'])
            ->orderBy('tgl_masuk')
            ->get();

        // Grup: belum triase, lalu per kategori
        $belumTriase = $kunjunganIgd->whereNull('triase');

        $grouped = $kunjunganIgd
            ->filter(fn($k) => $k->triase)
            ->groupBy(fn($k) => $k->triase->kategori)
            ->sortBy(fn($items, $key) => match ($key) {
                'MERAH' => 1,
                'KUNING' => 2,
                'HIJAU' => 3,
                'HITAM' => 4,
                default => 5,
            });

        $stats = [
            'merah' => $grouped->get('MERAH', collect())->count(),
            'kuning' => $grouped->get('KUNING', collect())->count(),
            'hijau' => $grouped->get('HIJAU', collect())->count(),
            'hitam' => $grouped->get('HITAM', collect())->count(),
            'belum_triase' => $belumTriase->count(),
        ];

        return view('igd.board', compact('grouped', 'belumTriase', 'stats'));
    }

    /**
     * Form triase IGD.
     */
    public function triaseForm(Kunjungan $kunjungan)
    {
        $this->authorize('triase', $kunjungan);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 404);
        abort_if($kunjungan->triase, 422, 'Pasien sudah ditriase');

        return view('igd.triase', compact('kunjungan'));
    }

    /**
     * Simpan triase IGD.
     */
    public function triaseStore(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('triase', $kunjungan);

        $data = $request->validate([
            'kategori' => 'required|in:MERAH,KUNING,HIJAU,HITAM',
            'keluhan_utama' => 'required|string|min:5|max:1000',
            'tanda_vital' => 'nullable|array',
            'tanda_vital.td_sistol' => 'nullable|integer|min:40|max:300',
            'tanda_vital.td_diastol' => 'nullable|integer|min:20|max:200',
            'tanda_vital.nadi' => 'nullable|integer|min:20|max:300',
            'tanda_vital.respirasi' => 'nullable|integer|min:5|max:80',
            'tanda_vital.suhu' => 'nullable|numeric|min:25|max:45',
            'tanda_vital.spo2' => 'nullable|integer|min:50|max:100',
            'tanda_vital.gcs' => 'nullable|string|max:20',
        ]);

        $this->triaseService->triase($kunjungan, $data, auth()->id());

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', "Triase {$data['kategori']} berhasil disimpan.");
    }

    /**
     * Halaman pemeriksaan IGD (mirip RJ tapi tanpa antrian/poli).
     */
    public function periksa(Kunjungan $kunjungan)
    {
        $this->authorize('periksa', $kunjungan);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 404);

        // Auto-create RawatJalan record kalau belum ada
        if (! $kunjungan->rawatJalan) {
            \App\Models\RawatJalan::create([
                'kunjungan_id' => $kunjungan->id,
                'poli_id' => null,
                'dokter_id' => auth()->user()->dokter?->id,
                'no_antrian' => null,
                'waktu_mulai_periksa' => now(),
            ]);
            $kunjungan->refresh();
        }

        $kunjungan->load(['pasien.rekamMedis', 'triase.petugas', 'rawatJalan.dokter', 'diagnosa.icd10']);

        return view('igd.periksa', compact('kunjungan'));
    }

    public function periksaStore(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('periksa', $kunjungan);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 404);

        $data = $request->validate([
            'tanda_vital' => 'nullable|array',
            'tanda_vital.td_sistol'  => 'nullable|numeric|min:40|max:300',
            'tanda_vital.td_diastol' => 'nullable|numeric|min:20|max:200',
            'tanda_vital.nadi'       => 'nullable|numeric|min:20|max:300',
            'tanda_vital.respirasi'  => 'nullable|numeric|min:5|max:80',
            'tanda_vital.suhu'       => 'nullable|numeric|min:25|max:45',
            'tanda_vital.spo2'       => 'nullable|numeric|min:50|max:100',
            'tanda_vital.bb'         => 'nullable|numeric|min:0|max:300',
            'tanda_vital.tb'         => 'nullable|numeric|min:0|max:250',
            'tanda_vital.gcs'        => 'nullable|string|max:20',

            'subjective' => 'nullable|string|max:5000',
            'objective'  => 'nullable|string|max:5000',
            'assessment' => 'nullable|string|max:2000',
            'plan'       => 'nullable|string|max:5000',
            'edukasi'    => 'nullable|string|max:2000',

            'diagnosa_baru' => 'nullable|array',
            'diagnosa_baru.*.icd10_kode' => 'required_with:diagnosa_baru|exists:icd10,kode',
            'diagnosa_baru.*.tipe' => 'required_with:diagnosa_baru|in:PRIMER,SEKUNDER,KOMPLIKASI',
            'diagnosa_baru.*.catatan' => 'nullable|string|max:500',

            'action' => 'required|in:save_draft,save_continue',
        ]);

        // Diagnosa wajib min. 1 PRIMER hanya saat "Simpan & Lanjut".
        // Draft boleh disimpan kosong.
        if ($data['action'] === 'save_continue') {
            $existingTotal = $kunjungan->diagnosa()->count();
            $existingPrimer = $kunjungan->diagnosa()->where('tipe', 'PRIMER')->count();
            $newList = collect($data['diagnosa_baru'] ?? []);
            $newPrimer = $newList->where('tipe', 'PRIMER')->count();

            if ($existingTotal + $newList->count() < 1) {
                return back()
                    ->withErrors(['diagnosa_baru' => 'Diagnosa (ICD-10) wajib diisi minimal 1 sebelum Simpan & Lanjut.'])
                    ->withInput();
            }

            if ($existingPrimer + $newPrimer < 1) {
                return back()
                    ->withErrors(['diagnosa_baru' => 'Minimal 1 diagnosa harus bertipe PRIMER sebelum Simpan & Lanjut.'])
                    ->withInput();
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($kunjungan, $data) {
            // Update RawatJalan record
            $rj = $kunjungan->rawatJalan;
            $rj->update([
                'tanda_vital' => array_filter($data['tanda_vital'] ?? [], fn($v) => $v !== null && $v !== ''),
                'subjective' => $data['subjective'] ?? null,
                'objective'  => $data['objective'] ?? null,
                'assessment' => $data['assessment'] ?? null,
                'plan'       => $data['plan'] ?? null,
                'edukasi'    => $data['edukasi'] ?? null,
            ]);

            // Insert diagnosa baru
            foreach ($data['diagnosa_baru'] ?? [] as $dx) {
                \App\Models\Diagnosa::create([
                    'kunjungan_id' => $kunjungan->id,
                    'icd10_kode' => $dx['icd10_kode'],
                    'tipe' => $dx['tipe'],
                    'catatan' => $dx['catatan'] ?? null,
                ]);
            }

            // Update status kunjungan jadi DALAM_PEMERIKSAAN
            if ($kunjungan->status === \App\Enums\StatusKunjungan::Terdaftar) {
                $kunjungan->update(['status' => \App\Enums\StatusKunjungan::DalamPemeriksaan]);
            }
        });

        $msg = $data['action'] === 'save_continue'
            ? 'Pemeriksaan disimpan. Silakan lanjut ke disposisi.'
            : 'Draft pemeriksaan tersimpan.';

        return redirect()->route('kunjungan.show', $kunjungan)->with('success', $msg);
    }

    /**
     * AJAX search ICD-10 untuk autocomplete.
     */
    public function icd10Search(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        return \App\Models\Icd10::query()
            ->where('kode', 'like', "%{$q}%")
            ->orWhere('nama', 'like', "%{$q}%")
            ->limit(15)
            ->get(['kode', 'nama']);
    }

    /**
     * ============================================================
     * DISPOSISI 1: ADMISI RAWAT INAP
     * IGD → RI cito. Reuse RawatInapService.
     * ============================================================
     */
    public function admisi(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('admisi', RawatInap::class);

        $data = $request->validate([
            'dpjp_id' => 'required|exists:dokter,id',
            'kamar_id' => 'required|exists:kamar,id',
            'alasan_masuk' => 'required|string|min:10|max:1000',
        ]);

        // Guard: harus IGD dan sudah triase + ada diagnosa
        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 422, 'Bukan kunjungan IGD');
        abort_unless($kunjungan->triase, 422, 'Pasien belum ditriase');
        abort_unless($kunjungan->diagnosa->isNotEmpty(), 422, 'Diagnosa belum diinput');

        $ri = $this->riService->admisiDariIgd($kunjungan, $data, auth()->id());

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', "Pasien berhasil diadmisi ke kamar {$ri->kamarInap->first()->kamar->no_kamar}. DPJP: {$ri->dpjp->nama_lengkap}");
    }

    /**
     * ============================================================
     * DISPOSISI 2: RUJUK KE RS LAIN
     * ============================================================
     */
    public function rujuk(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('disposisi', $kunjungan);

        $data = $request->validate([
            'rs_tujuan' => 'required|string|max:255',
            'alasan_rujuk' => 'required|in:FASILITAS_TIDAK_TERSEDIA,SPESIALIS_TIDAK_TERSEDIA,KAMAR_PENUH,ICU_PENUH,PERMINTAAN_PASIEN,LAINNYA',
            'catatan_rujuk' => 'required|string|min:10|max:2000',
            'transportasi' => 'nullable|in:AMBULANS_RS,AMBULANS_LAIN,KENDARAAN_PRIBADI',
        ]);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 422);

        DB::transaction(function () use ($kunjungan, $data) {
            RujukanKeluar::create([
                'kunjungan_id' => $kunjungan->id,
                'rs_tujuan' => $data['rs_tujuan'],
                'alasan' => $data['alasan_rujuk'],
                'catatan' => $data['catatan_rujuk'],
                'transportasi' => $data['transportasi'] ?? null,
                'tgl_rujuk' => now(),
                'dirujuk_oleh' => auth()->id(),
            ]);

            $kunjungan->update([
                'tgl_keluar' => now(),
                'status' => StatusKunjungan::MenungguPembayaran,
                'cara_keluar' => 'RUJUK',
            ]);
        });

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', "Pasien berhasil dirujuk ke {$data['rs_tujuan']}. Lanjut ke billing.");
    }


    /**
     * ============================================================
     * DISPOSISI 3: PULANG DARI IGD
     * ============================================================
     */
    public function pulangIgd(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('disposisi', $kunjungan);

        $data = $request->validate([
            'kondisi_pulang' => 'required|in:SEMBUH,MEMBAIK,STABIL,APS',
            'instruksi_pulang' => 'required|string|min:10|max:2000',
        ]);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 422);

        DB::transaction(function () use ($kunjungan, $data) {
            // Update rawat jalan IGD dengan instruksi (kalau ada)
            if ($kunjungan->rawatJalan) {
                $kunjungan->rawatJalan->update([
                    'edukasi' => $kunjungan->rawatJalan->edukasi
                        ? $kunjungan->rawatJalan->edukasi . "\n\n=== INSTRUKSI PULANG ===\n" . $data['instruksi_pulang']
                        : $data['instruksi_pulang'],
                ]);
            }

            $kunjungan->update([
                'tgl_keluar' => now(),
                'status' => StatusKunjungan::MenungguPembayaran,
                'cara_keluar' => $data['kondisi_pulang'],
            ]);
        });

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', 'Pasien telah pulang dari IGD. Lanjutkan ke billing.');
    }

    /**
     * ============================================================
     * DISPOSISI 4: MENINGGAL DI IGD
     * ============================================================
     */
    public function meninggal(Request $request, Kunjungan $kunjungan)
    {
        $this->authorize('disposisi', $kunjungan);

        $data = $request->validate([
            'tgl_meninggal' => 'required|date|before_or_equal:now',
            'sebab_kematian' => 'required|string|min:5|max:1000',
            'status_tiba' => 'nullable|in:HIDUP,DOA',
        ]);

        abort_unless($kunjungan->tipe === TipeKunjungan::IGD, 422);

        DB::transaction(function () use ($kunjungan, $data) {
            // Update pasien dengan info kematian
            $kunjungan->pasien->update([
                'tgl_meninggal' => $data['tgl_meninggal'],
                'sebab_kematian' => $data['sebab_kematian'],
                'status_hidup' => 'MENINGGAL',
            ]);

            // Tutup kunjungan
            $kunjungan->update([
                'tgl_keluar' => $data['tgl_meninggal'],
                'status' => StatusKunjungan::MenungguPembayaran,
                'cara_keluar' => $data['status_tiba'] === 'DOA' ? 'DOA' : 'MENINGGAL',
            ]);
        });

        return redirect()
            ->route('kunjungan.show', $kunjungan)
            ->with('success', 'Kematian telah dicatat. Surat keterangan kematian dapat dicetak dari menu Surat.');
    }
}
