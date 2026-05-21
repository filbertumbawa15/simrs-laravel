@extends('layouts.app')

@section('title', 'Eksekusi Radiologi')
@section('page-header', true)
@section('page-title', 'Eksekusi: '.$order->no_order)
@section('page-subtitle', $order->kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('rad.eksekusi.store', $order) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    @if ($order->hamil)
        <div class="alert alert-error">
            <strong>⚠ PASIEN HAMIL</strong> — pastikan menggunakan APRON dan teknik radiasi minimum.
            Jika modalitas X-ray, konfirmasi ulang ke dokter perujuk!
        </div>
    @endif

    {{-- Daftar pemeriksaan --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Pemeriksaan yang Diorder</h3></div>
        <div class="card-body">
            <ul class="space-y-1">
                @foreach ($order->details as $d)
                    <li class="flex items-center justify-between text-sm">
                        <span>• {{ $d->pemeriksaan->nama }}</span>
                        <span class="badge badge-gray text-xs">{{ $d->pemeriksaan->modalitas->label() }}</span>
                    </li>
                @endforeach
            </ul>
            @if ($order->klinis)
                <div class="mt-3 p-2 bg-blue-50 rounded text-sm">
                    <strong>Klinis:</strong> {{ $order->klinis }}
                </div>
            @endif
        </div>
    </div>

    {{-- Upload images --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Upload Gambar Hasil</h3>
            <span class="text-xs text-gray-500">JPG/PNG/PDF/DCM, max 20MB per file</span>
        </div>
        <div class="card-body">
            <input type="file" name="images[]" multiple accept="image/*,.pdf,.dcm"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg
                          file:border-0 file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            <p class="help">Tip: gunakan label di file name (misal "AP_view.jpg", "Lateral.jpg") agar mudah diidentifikasi.</p>

            {{-- Image existing --}}
            @if ($order->images->isNotEmpty())
                <div class="mt-4 pt-4 border-t">
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Sudah diupload:</div>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        @foreach ($order->images as $img)
                            <div class="relative">
                                @if ($img->is_image)
                                    <img src="{{ route('rad.image.view', $img) }}" class="w-full h-20 object-cover rounded border">
                                @else
                                    <div class="w-full h-20 bg-gray-100 rounded border flex items-center justify-center text-2xl">📄</div>
                                @endif
                                <div class="text-[10px] truncate mt-0.5">{{ basename($img->path) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Kondisi teknis --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Kondisi Teknis Pemeriksaan</h3></div>
        <div class="card-body">
            <textarea name="kondisi_teknis" rows="3" class="textarea"
                      placeholder="Posisi pasien, kV, mAs, jarak, faktor eksposi, kontras digunakan, dll.">{{ $order->kondisi_teknis }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('rad.show', $order) }}" class="btn-secondary">Batal</a>
        <button class="btn-primary btn-lg">💾 Simpan & Kirim ke Radiolog</button>
    </div>
</form>

@endsection
