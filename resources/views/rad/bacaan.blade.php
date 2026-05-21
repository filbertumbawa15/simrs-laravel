@extends('layouts.app')

@section('title', 'Bacaan Radiolog')
@section('page-header', true)
@section('page-title', 'Input Bacaan: '.$order->no_order)
@section('page-subtitle', $order->kunjungan->pasien->nama)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Image gallery (sticky) --}}
    <div class="lg:col-span-1">
        <div class="card sticky top-6">
            <div class="card-header">
                <h3 class="font-semibold text-sm">Gambar ({{ $order->images->count() }})</h3>
            </div>
            <div class="card-body space-y-2 max-h-[calc(100vh-150px)] overflow-y-auto">
                @forelse ($order->images as $img)
                    @if ($img->is_image)
                        <a href="{{ route('rad.image.view', $img) }}" target="_blank">
                            <img src="{{ route('rad.image.view', $img) }}" class="w-full rounded border hover:border-primary-500">
                        </a>
                    @else
                        <a href="{{ route('rad.image.view', $img) }}" target="_blank"
                           class="block bg-gray-100 rounded border p-4 text-center text-gray-500 hover:border-primary-500">
                            📄 {{ basename($img->path) }}
                        </a>
                    @endif
                    @if ($img->label)<div class="text-xs text-gray-500 text-center -mt-1">{{ $img->label }}</div>@endif
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada image diupload</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Form bacaan per pemeriksaan --}}
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('rad.bacaan.store', $order) }}" class="space-y-4">
            @csrf

            {{-- Info klinis dari dokter --}}
            @if ($order->klinis || $order->diagnosa_kerja)
                <div class="alert alert-info">
                    @if ($order->diagnosa_kerja)<div><strong>Dx Kerja:</strong> {{ $order->diagnosa_kerja }}</div>@endif
                    @if ($order->klinis)<div><strong>Klinis:</strong> {{ $order->klinis }}</div>@endif
                </div>
            @endif

            @foreach ($order->details as $d)
                @php $existing = $order->hasil->firstWhere('pemeriksaan_id', $d->pemeriksaan_id); @endphp
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold">{{ $d->pemeriksaan->nama }}</h3>
                        <span class="text-xs text-gray-500">{{ $d->pemeriksaan->modalitas->label() }}</span>
                    </div>
                    <div class="card-body space-y-3">
                        @if ($d->pemeriksaan->template_bacaan && ! $existing?->bacaan)
                            <div class="text-xs">
                                <button type="button"
                                        onclick="document.getElementById('bacaan-{{ $d->pemeriksaan_id }}').value = @js($d->pemeriksaan->template_bacaan)"
                                        class="text-primary-600 hover:underline">
                                    📋 Gunakan template default
                                </button>
                            </div>
                        @endif

                        <div>
                            <label class="label text-xs">Bacaan / Deskripsi</label>
                            <textarea name="hasil[{{ $d->pemeriksaan_id }}][bacaan]"
                                      id="bacaan-{{ $d->pemeriksaan_id }}"
                                      rows="5" class="textarea text-sm font-mono"
                                      placeholder="Deskripsikan temuan radiologis…">{{ $existing?->bacaan }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="label text-xs">Kesan</label>
                                <textarea name="hasil[{{ $d->pemeriksaan_id }}][kesan]" rows="3" class="textarea text-sm"
                                          placeholder="Kesimpulan klinis radiolog…">{{ $existing?->kesan }}</textarea>
                            </div>
                            <div>
                                <label class="label text-xs">Saran</label>
                                <textarea name="hasil[{{ $d->pemeriksaan_id }}][saran]" rows="3" class="textarea text-sm"
                                          placeholder="Pemeriksaan tambahan, korelasi klinis, follow-up…">{{ $existing?->saran }}</textarea>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 p-2 bg-red-50 rounded">
                            <input type="checkbox" name="hasil[{{ $d->pemeriksaan_id }}][ada_temuan_kritis]" value="1"
                                   @checked($existing?->ada_temuan_kritis)
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-red-700">⚠ Temuan kritis (akan auto-notifikasi DPJP)</span>
                        </label>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('rad.show', $order) }}" class="btn-secondary">Batal</a>
                <button class="btn-primary btn-lg">💾 Simpan Bacaan</button>
            </div>
        </form>
    </div>
</div>

@endsection
