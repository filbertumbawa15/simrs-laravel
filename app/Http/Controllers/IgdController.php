<?php

namespace App\Http\Controllers;

use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\TriaseIgd;
use App\Services\TriaseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IgdController extends Controller
{
    public function __construct(protected TriaseService $service) {}

    /**
     * Board IGD: daftar pasien aktif diurutkan PRIORITAS (Merah dulu).
     */
    public function board(): View
    {
        $kunjungan = Kunjungan::query()
            ->with(['pasien', 'triase.petugas'])
            ->where('tipe', TipeKunjungan::IGD)
            ->whereNotIn('status', ['SELESAI', 'BATAL'])
            ->whereDate('tgl_masuk', '>=', now()->subDays(2))
            ->get()
            ->sortBy([
                fn ($a, $b) => $this->prioritasOrder($a->triase?->kategori)
                    <=> $this->prioritasOrder($b->triase?->kategori),
                fn ($a, $b) => $a->tgl_masuk <=> $b->tgl_masuk,
            ])
            ->values();

        // Group untuk header per kategori
        $grouped = $kunjungan->groupBy(fn ($k) => $k->triase?->kategori ?? 'BELUM_TRIASE');

        return view('igd.board', compact('grouped'));
    }

    /**
     * Form triase awal.
     */
    public function triaseForm(Kunjungan $kunjungan): View
    {
        if ($kunjungan->tipe !== TipeKunjungan::IGD) {
            abort(403, 'Hanya untuk kunjungan IGD.');
        }

        return view('igd.triase', compact('kunjungan'));
    }

    public function triaseStore(Request $request, Kunjungan $kunjungan)
    {
        $data = $request->validate([
            'kategori' => ['required', 'in:MERAH,KUNING,HIJAU,HITAM'],
            'keluhan_utama' => ['required', 'string', 'max:1000'],
            'tanda_vital' => ['nullable', 'array'],
            'tanda_vital.td_sistol' => ['nullable', 'integer', 'between:0,300'],
            'tanda_vital.td_diastol' => ['nullable', 'integer', 'between:0,200'],
            'tanda_vital.nadi' => ['nullable', 'integer', 'between:0,250'],
            'tanda_vital.respirasi' => ['nullable', 'integer', 'between:0,80'],
            'tanda_vital.suhu' => ['nullable', 'numeric', 'between:25,45'],
            'tanda_vital.spo2' => ['nullable', 'integer', 'between:0,100'],
            'tanda_vital.gcs' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $this->service->triase(
                kunjungan: $kunjungan,
                kategori: $data['kategori'],
                keluhanUtama: $data['keluhan_utama'],
                tandaVital: $data['tanda_vital'] ?? [],
                petugasId: auth()->id(),
            );

            return redirect()->route('igd.board')
                ->with('success', "Triase {$data['kategori']} berhasil. Pasien masuk antrian sesuai prioritas.");
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Urutan prioritas untuk sorting.
     */
    protected function prioritasOrder(?string $kategori): int
    {
        return match ($kategori) {
            'MERAH' => 1,
            'KUNING' => 2,
            'HIJAU' => 3,
            'HITAM' => 4,
            default => 0, // Belum triase = paling atas (perlu segera ditangani)
        };
    }
}
