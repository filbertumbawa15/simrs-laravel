<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasienRequest;
use App\Models\Pasien;
use App\Services\PendaftaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasienController extends Controller
{
    public function __construct(protected PendaftaranService $pendaftaran) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pasien::class);

        $pasien = Pasien::query()
            ->search($request->input('q'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pasien.index', compact('pasien'));
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
