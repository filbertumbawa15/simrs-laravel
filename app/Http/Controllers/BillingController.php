<?php

namespace App\Http\Controllers;

use App\Enums\MetodePembayaran;
use App\Models\Kunjungan;
use App\Models\Tagihan;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(protected BillingService $service) {}

    public function index(Request $request): View
    {
        $tagihan = Tagihan::query()
            ->with(['kunjungan.pasien'])
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('tanggal'), fn($q, $d) => $q->whereDate('tgl_tagihan', $d))
            ->latest('tgl_tagihan')
            ->paginate(20);

        return view('billing.index', compact('tagihan'));
    }

    /**
     * Generate tagihan dari kunjungan.
     */
    public function generate(Kunjungan $kunjungan)
    {
        $tagihan = $this->service->generateTagihan($kunjungan);

        return redirect()
            ->route('billing.show', $tagihan)
            ->with('success', "Tagihan {$tagihan->no_tagihan} dibuat. Periksa rincian sebelum finalisasi.");
    }

    public function show(Tagihan $tagihan): View
    {
        $tagihan->load([
            'kunjungan.pasien',
            'details',
            'pembayaran.kasir',
            'klaimBpjs',
        ]);

        return view('billing.show', compact('tagihan'));
    }

    public function finalize(Tagihan $tagihan)
    {
        try {
            $this->service->finalize($tagihan, auth()->id());

            return back()->with('success', 'Tagihan difinalisasi. Siap diproses pembayaran.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form pembayaran (kasir).
     */
    public function bayarForm(Tagihan $tagihan): View
    {
        $tagihan->load(['kunjungan.pasien', 'details']);

        return view('billing.bayar', compact('tagihan'));
    }

    public function bayar(Request $request, Tagihan $tagihan)
    {
        $data = $request->validate([
            'metode' => ['required', Rule::in(array_column(MetodePembayaran::cases(), 'value'))],
            'jumlah' => ['required', 'numeric', 'min:1', "max:{$tagihan->sisa}"],
            'referensi_eksternal' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);

        try {
            $this->service->catatPembayaran(
                tagihan: $tagihan,
                metode: MetodePembayaran::from($data['metode']),
                jumlah: (float) $data['jumlah'],
                kasirId: auth()->id(),
                referensi: $data['referensi_eksternal'] ?? null,
                catatan: $data['catatan'] ?? null,
            );

            return redirect()
                ->route('billing.show', $tagihan)
                ->with('success', 'Pembayaran tercatat.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
