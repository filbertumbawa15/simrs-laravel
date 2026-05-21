<?php

namespace App\Http\Controllers;

use App\Enums\StatusKunjungan;
use App\Http\Requests\StoreSoapRequest;
use App\Models\Diagnosa;
use App\Models\Icd10;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Services\RawatJalanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RawatJalanController extends Controller
{
    public function __construct(protected RawatJalanService $service) {}

    /**
     * Dashboard antrian — daftar pasien yang sedang antri di tiap poli.
     */
    public function antrian(Request $request): View
    {
        $poliId = $request->input('poli_id');

        $poli = Poli::active()->orderBy('nama')->get();

        $antrian = RawatJalan::query()
            ->with(['kunjungan.pasien', 'poli', 'dokter'])
            ->whereDate('created_at', today())
            ->when($poliId, fn($q) => $q->where('poli_id', $poliId))
            ->whereHas('kunjungan', fn($q) => $q->whereNotIn('status', ['SELESAI', 'BATAL']))
            ->orderBy('no_antrian')
            ->get()
            ->groupBy('poli_id');

        return view('rj.antrian', compact('poli', 'antrian', 'poliId'));
    }

    public function show(RawatJalan $rj): View
    {
        $rj->load([
            'kunjungan.pasien.rekamMedis',
            'kunjungan.diagnosa.icd10',
            'kunjungan.orderLab',
            'kunjungan.resep.details.obat',
            'poli',
            'dokter',
        ]);

        return view('rj.show', compact('rj'));
    }

    /**
     * Panggil pasien.
     */
    public function panggil(RawatJalan $rj)
    {
        $this->service->panggilPasien($rj);

        return back()->with('success', 'Pasien dipanggil.');
    }

    /**
     * Tampilkan form pemeriksaan SOAP.
     */
    public function periksa(RawatJalan $rj): View
    {
        $this->service->mulaiPeriksa($rj);

        $rj->load([
            'kunjungan.pasien.rekamMedis',
            'kunjungan.diagnosa.icd10',
            'poli',
            'dokter',
        ]);

        return view('rj.periksa', compact('rj'));
    }

    /**
     * Simpan SOAP + diagnosa.
     */
    public function simpanSoap(StoreSoapRequest $request, RawatJalan $rj)
    {
        DB::transaction(function () use ($request, $rj) {
            $data = $request->validated();
            $this->service->simpanSoap($rj, $data);

            // Sinkronkan diagnosa
            if (! empty($data['diagnosa'])) {
                $rj->kunjungan->diagnosa()->delete();
                foreach ($data['diagnosa'] as $dx) {
                    Diagnosa::create([
                        'kunjungan_id' => $rj->kunjungan_id,
                        'icd10_kode' => $dx['icd10_kode'],
                        'tipe' => $dx['tipe'],
                        'catatan' => $dx['catatan'] ?? null,
                        'dokter_id' => $rj->dokter_id,
                    ]);
                }
            }
        });

        return back()->with('success', 'SOAP & diagnosa tersimpan.');
    }

    /**
     * Selesaikan pemeriksaan (semua dokumentasi sudah lengkap).
     */
    public function selesai(RawatJalan $rj)
    {
        // Validasi minimum: harus ada minimal 1 diagnosa primer
        if (! $rj->kunjungan->diagnosa()->where('tipe', 'PRIMER')->exists()) {
            return back()->with('error', 'Wajib mengisi minimal 1 diagnosa primer sebelum menyelesaikan pemeriksaan.');
        }

        $this->service->selesaikanPemeriksaan($rj);

        return redirect()
            ->route('rj.antrian')
            ->with('success', 'Pemeriksaan selesai. Pasien diarahkan ke ' . $rj->kunjungan->fresh()->status->label() . '.');
    }

    /**
     * AJAX endpoint untuk autocomplete ICD-10.
     */
    public function searchIcd(Request $request)
    {
        $term = $request->input('q');

        return Icd10::search($term)
            ->where('is_active', true)
            ->limit(20)
            ->get(['kode', 'nama']);
    }
}
