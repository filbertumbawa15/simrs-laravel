<?php

namespace App\Http\Controllers;

use App\Enums\PrioritasOrder;
use App\Http\Requests\StoreOrderLabRequest;
use App\Models\Kunjungan;
use App\Models\OrderLab;
use App\Models\ParameterLab;
use App\Services\LabService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    private const SORTABLE = ['no_order', 'tgl_order', 'status', 'prioritas'];

    public function __construct(protected LabService $service) {}

    /**
     * Worklist analis lab: order yang perlu diproses.
     */
    public function index(): View
    {
        return view('lab.index');
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));
        $q = trim((string) $request->input('q', ''));

        $query = OrderLab::query()
            ->with(['kunjungan.pasien', 'dokter', 'details.parameter'])
            ->when($request->input('status'), fn ($qq, $s) => $qq->where('status', $s))
            ->when($request->input('prioritas'), fn ($qq, $p) => $qq->where('prioritas', $p))
            ->when($q !== '', fn ($qq) => $qq->where(function ($x) use ($q) {
                $x->where('no_order', 'like', "%{$q}%")
                  ->orWhereHas('kunjungan.pasien', fn($p) => $p->where('nama', 'like', "%{$q}%")
                      ->orWhere('no_rm', 'like', "%{$q}%"));
            }));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest('tgl_order');
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($o) => [
                'id' => $o->id,
                'no_order' => $o->no_order,
                'tgl_order' => $o->tgl_order?->format('d M Y H:i'),
                'pasien' => [
                    'nama' => $o->kunjungan->pasien->nama,
                    'no_rm' => $o->kunjungan->pasien->no_rm,
                ],
                'dokter' => $o->dokter->nama_lengkap,
                'status' => $o->status?->value,
                'status_label' => $o->status?->label(),
                'prioritas' => $o->prioritas?->value,
                'prioritas_label' => $o->prioritas?->label(),
                'jumlah_parameter' => $o->details->count(),
                'url' => route('lab.show', $o),
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
     * Form buat order lab dari halaman pemeriksaan dokter.
     */
    public function create(Request $request): View
    {
        $kunjungan = Kunjungan::with('pasien', 'rawatJalan.dokter')
            ->findOrFail($request->input('kunjungan_id'));

        $parameters = ParameterLab::where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get()
            ->groupBy('kategori');

        return view('lab.create-order', compact('kunjungan', 'parameters'));
    }

    public function store(StoreOrderLabRequest $request)
    {
        $data = $request->validated();

        $order = $this->service->buatOrder(
            kunjunganId: $data['kunjungan_id'],
            dokterId: $data['dokter_id'],
            parameterIds: $data['parameter_ids'],
            prioritas: PrioritasOrder::from($data['prioritas']),
            catatanKlinis: $data['catatan_klinis'] ?? null,
            diagnosaKerja: $data['diagnosa_kerja'] ?? null,
        );

        return redirect()
            ->route('lab.show', $order)
            ->with('success', "Order {$order->no_order} berhasil dibuat.");
    }

    public function show(OrderLab $order): View
    {
        $order->load([
            'kunjungan.pasien',
            'dokter',
            'details.parameter',
            'hasil.parameter',
            'hasil.validator',
            'validator',
        ]);

        return view('lab.show-order', compact('order'));
    }

    /**
     * Tandai sampel sudah diambil.
     */
    public function sampling(OrderLab $order)
    {
        try {
            $this->service->tandaiSampelDiambil($order, auth()->id());

            return back()->with('success', 'Sampel ditandai sudah diambil. Lanjut ke proses pemeriksaan.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mulai proses (analisa di analyzer/manual).
     */
    public function proses(OrderLab $order)
    {
        $this->service->mulaiProses($order);

        return redirect()
            ->route('lab.input', $order)
            ->with('success', 'Status diubah ke "Diproses". Silakan input hasil.');
    }

    /**
     * Form input hasil per parameter.
     */
    public function inputForm(OrderLab $order): View
    {
        $order->load(['kunjungan.pasien', 'details.parameter', 'hasil']);

        return view('lab.input-hasil', compact('order'));
    }

    public function inputStore(Request $request, OrderLab $order)
    {
        $data = $request->validate([
            'hasil' => ['required', 'array'],
            'hasil.*.hasil' => ['nullable', 'string', 'max:255'],
            'hasil.*.catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->inputHasil($order, $data['hasil'], auth()->id());

        return redirect()
            ->route('lab.show', $order)
            ->with('success', 'Hasil tersimpan. Menunggu validasi dokter PK.');
    }

    /**
     * Dokter PK validasi hasil.
     */
    public function validate_(OrderLab $order)
    {
        $this->authorize('lab.validate', $order);

        try {
            $this->service->validasiHasil($order, auth()->id());

            return back()->with('success', 'Hasil divalidasi. Notifikasi nilai kritis (jika ada) sudah dikirim ke DPJP.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
