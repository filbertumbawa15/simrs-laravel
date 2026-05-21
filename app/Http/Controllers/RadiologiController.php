<?php

namespace App\Http\Controllers;

use App\Enums\PrioritasOrder;
use App\Http\Requests\StoreOrderRadiologiRequest;
use App\Models\HasilRadiologiImage;
use App\Models\Kunjungan;
use App\Models\OrderRadiologi;
use App\Models\PemeriksaanRadiologi;
use App\Services\RadiologiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RadiologiController extends Controller
{
    public function __construct(protected RadiologiService $service) {}

    /**
     * Worklist radiografer & radiolog.
     */
    public function index(Request $request): View
    {
        $orders = OrderRadiologi::query()
            ->with(['kunjungan.pasien', 'dokter', 'details.pemeriksaan'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('prioritas'), fn ($q, $p) => $q->where('prioritas', $p))
            ->latest('tgl_order')
            ->paginate(20);

        return view('rad.index', compact('orders'));
    }

    /**
     * Form buat order (dari halaman pemeriksaan dokter).
     */
    public function create(Request $request): View
    {
        $kunjungan = Kunjungan::with('pasien', 'rawatJalan.dokter')
            ->findOrFail($request->input('kunjungan_id'));

        $pemeriksaans = PemeriksaanRadiologi::where('is_active', true)
            ->orderBy('modalitas')
            ->orderBy('nama')
            ->get()
            ->groupBy('modalitas.value');

        return view('rad.create-order', compact('kunjungan', 'pemeriksaans'));
    }

    public function store(StoreOrderRadiologiRequest $request)
    {
        $data = $request->validated();

        $order = $this->service->buatOrder(
            kunjunganId: $data['kunjungan_id'],
            dokterId: $data['dokter_id'],
            pemeriksaanIds: $data['pemeriksaan_ids'],
            prioritas: PrioritasOrder::from($data['prioritas']),
            klinis: $data['klinis'] ?? null,
            diagnosaKerja: $data['diagnosa_kerja'] ?? null,
            hamil: (bool)($data['hamil'] ?? false),
            persiapanPuasa: (bool)($data['persiapan_puasa'] ?? false),
        );

        return redirect()->route('rad.show', $order)
            ->with('success', "Order {$order->no_order} dibuat.");
    }

    public function show(OrderRadiologi $order): View
    {
        $order->load([
            'kunjungan.pasien', 'dokter',
            'details.pemeriksaan', 'hasil.pemeriksaan',
            'images.uploader', 'radiografer', 'radiolog',
        ]);
        return view('rad.show-order', compact('order'));
    }

    /**
     * Form eksekusi (radiografer).
     */
    public function eksekusiForm(OrderRadiologi $order): View
    {
        $order->load(['kunjungan.pasien', 'details.pemeriksaan', 'images']);
        return view('rad.eksekusi', compact('order'));
    }

    public function eksekusiStore(Request $request, OrderRadiologi $order)
    {
        $data = $request->validate([
            'kondisi_teknis' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,pdf,dcm', 'max:20480'], // 20MB per file
        ]);

        try {
            $this->service->eksekusiPemeriksaan(
                order: $order,
                radiograferId: auth()->id(),
                kondisiTeknis: $data['kondisi_teknis'] ?? null,
                files: $request->file('images', []),
            );

            return redirect()->route('rad.show', $order)
                ->with('success', 'Eksekusi tercatat. Order menunggu bacaan radiolog.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form bacaan radiolog.
     */
    public function bacaanForm(OrderRadiologi $order): View
    {
        $order->load(['kunjungan.pasien', 'details.pemeriksaan', 'hasil', 'images']);
        return view('rad.bacaan', compact('order'));
    }

    public function bacaanStore(Request $request, OrderRadiologi $order)
    {
        $data = $request->validate([
            'hasil' => ['required', 'array'],
            'hasil.*.bacaan' => ['nullable', 'string'],
            'hasil.*.kesan' => ['nullable', 'string'],
            'hasil.*.saran' => ['nullable', 'string'],
            'hasil.*.ada_temuan_kritis' => ['nullable', 'boolean'],
        ]);

        foreach ($data['hasil'] as $pemeriksaanId => $row) {
            $this->service->inputBacaan(
                order: $order,
                pemeriksaanId: $pemeriksaanId,
                radiologId: auth()->id(),
                bacaan: $row['bacaan'] ?? null,
                kesan: $row['kesan'] ?? null,
                saran: $row['saran'] ?? null,
                adaTemuanKritis: (bool)($row['ada_temuan_kritis'] ?? false),
            );
        }

        return redirect()->route('rad.show', $order)
            ->with('success', 'Bacaan tersimpan. Klik "Validasi" jika sudah final.');
    }

    public function validasi(OrderRadiologi $order)
    {
        try {
            $this->service->validasi($order, auth()->id());
            return back()->with('success', 'Hasil divalidasi & dirilis ke dokter perujuk.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus image (sebelum validasi).
     */
    public function deleteImage(HasilRadiologiImage $image)
    {
        $orderId = $image->order_id;
        try {
            $this->service->hapusImage($image);
            return back()->with('success', 'Image dihapus.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Stream image privately. Hanya user authenticated yang bisa akses.
     */
    public function viewImage(HasilRadiologiImage $image): StreamedResponse
    {
        $disk = Storage::disk($image->disk);
        if (! $disk->exists($image->path)) {
            abort(404);
        }

        return $disk->response($image->path, basename($image->path), [
            'Content-Type' => $image->mime,
        ]);
    }
}
