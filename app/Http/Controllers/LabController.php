<?php

namespace App\Http\Controllers;

use App\Enums\PrioritasOrder;
use App\Http\Requests\StoreOrderLabRequest;
use App\Models\Kunjungan;
use App\Models\OrderLab;
use App\Models\ParameterLab;
use App\Services\LabService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    public function __construct(protected LabService $service) {}

    /**
     * Worklist analis lab: order yang perlu diproses.
     */
    public function index(Request $request): View
    {
        $orders = OrderLab::query()
            ->with(['kunjungan.pasien', 'dokter', 'details.parameter'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('prioritas'), fn ($q, $p) => $q->where('prioritas', $p))
            ->latest('tgl_order')
            ->paginate(20);

        return view('lab.index', compact('orders'));
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
