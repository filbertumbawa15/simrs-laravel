<?php

namespace App\Http\Controllers;

use App\Enums\MetodePembayaran;
use App\Models\Kunjungan;
use App\Models\Tagihan;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    private const SORTABLE = ['no_tagihan', 'tgl_tagihan', 'total', 'sisa', 'status'];

    public function __construct(protected BillingService $service) {}

    public function index(): View
    {
        return view('billing.index');
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));
        $q = trim((string) $request->input('q', ''));

        $query = Tagihan::query()
            ->with(['kunjungan.pasien'])
            ->when($request->input('status'), fn($qq, $s) => $qq->where('status', $s))
            ->when($request->input('tanggal'), fn($qq, $d) => $qq->whereDate('tgl_tagihan', $d))
            ->when($q !== '', fn($qq) => $qq->where(function ($x) use ($q) {
                $x->where('no_tagihan', 'like', "%{$q}%")
                  ->orWhereHas('kunjungan.pasien', fn($p) => $p->where('nama', 'like', "%{$q}%")
                      ->orWhere('no_rm', 'like', "%{$q}%"));
            }));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest('tgl_tagihan');
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($t) => [
                'id' => $t->id,
                'no_tagihan' => $t->no_tagihan,
                'tgl_tagihan' => $t->tgl_tagihan?->format('d M Y'),
                'pasien' => [
                    'nama' => $t->kunjungan->pasien->nama,
                    'no_rm' => $t->kunjungan->pasien->no_rm,
                ],
                'no_kunjungan' => $t->kunjungan->no_kunjungan,
                'total' => (float) $t->total,
                'dibayar' => (float) $t->dibayar,
                'sisa' => (float) $t->sisa,
                'status' => $t->status?->value,
                'status_label' => $t->status?->label(),
                'urls' => [
                    'show' => route('billing.show', $t),
                    'bayar' => route('billing.bayar.form', $t),
                ],
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
