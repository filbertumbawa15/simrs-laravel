@extends('layouts.app')

@section('title', $order->no_order)
@section('page-header', true)
@section('page-title', 'Order Radiologi '.$order->no_order)
@section('page-subtitle', $order->kunjungan->pasien->nama.' • '.$order->tgl_order->format('d M Y H:i'))

@section('page-actions')
    @php $status = $order->status->value; @endphp
    @if (in_array($status, ['DIORDER', 'DILAKUKAN']))
        @can('rad.execute')
            <a href="{{ route('rad.eksekusi.form', $order) }}" class="btn-warning">📷 Eksekusi / Upload</a>
        @endcan
    @endif
    @if ($status === 'MENUNGGU_BACAAN')
        @can('rad.read')
            <a href="{{ route('rad.bacaan.form', $order) }}" class="btn-primary">📝 Input Bacaan</a>
        @endcan
    @endif
    @if ($status === 'MENUNGGU_BACAAN' && $order->hasil->count() === $order->details->count())
        @can('rad.validate')
            <form method="POST" action="{{ route('rad.validate', $order) }}" class="inline">
                @csrf
                <button class="btn-success" onclick="return confirm('Validasi & rilis hasil ke dokter perujuk?')">
                    ✓ Validasi
                </button>
            </form>
        @endcan
    @endif
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Info order --}}
        <div class="card">
            <div class="card-body grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Status</div>
                    <span class="badge badge-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Prioritas</div>
                    @if ($order->prioritas->value === 'CITO')
                        <span class="badge badge-red font-bold">⚡ CITO</span>
                    @else
                        <span class="badge badge-gray">Rutin</span>
                    @endif
                </div>
                <div>
                    <div class="text-xs text-gray-500">Dokter Perujuk</div>
                    <div class="font-medium text-xs">{{ $order->dokter->nama_lengkap }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Catatan</div>
                    @if ($order->hamil)<span class="badge badge-yellow text-xs">⚠ Hamil</span>@endif
                    @if ($order->persiapan_puasa)<span class="badge badge-blue text-xs">Puasa</span>@endif
                </div>
            </div>
            @if ($order->klinis || $order->diagnosa_kerja)
                <div class="card-footer text-sm space-y-1">
                    @if ($order->diagnosa_kerja)<div><strong class="text-xs text-gray-500">Diagnosa Kerja:</strong> {{ $order->diagnosa_kerja }}</div>@endif
                    @if ($order->klinis)<div><strong class="text-xs text-gray-500">Klinis:</strong> {{ $order->klinis }}</div>@endif
                </div>
            @endif
        </div>

        {{-- Pemeriksaan + Hasil --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Pemeriksaan & Bacaan</h3></div>
            <div class="card-body space-y-4">
                @foreach ($order->details as $d)
                    @php $h = $order->hasil->firstWhere('pemeriksaan_id', $d->pemeriksaan_id); @endphp
                    <div class="border border-gray-200 rounded-lg p-4 {{ $h?->ada_temuan_kritis ? 'border-red-300 bg-red-50' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <div class="font-semibold">{{ $d->pemeriksaan->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $d->pemeriksaan->modalitas->label() }} · {{ $d->pemeriksaan->region }}</div>
                            </div>
                            @if ($h?->ada_temuan_kritis)
                                <span class="badge badge-red">⚠ Temuan Kritis</span>
                            @endif
                        </div>

                        @if ($h)
                            @if ($h->bacaan)
                                <div class="mt-2">
                                    <div class="text-xs font-semibold text-gray-500 uppercase">Bacaan</div>
                                    <p class="text-sm whitespace-pre-line">{{ $h->bacaan }}</p>
                                </div>
                            @endif
                            @if ($h->kesan)
                                <div class="mt-2">
                                    <div class="text-xs font-semibold text-gray-500 uppercase">Kesan</div>
                                    <p class="text-sm whitespace-pre-line font-medium">{{ $h->kesan }}</p>
                                </div>
                            @endif
                            @if ($h->saran)
                                <div class="mt-2">
                                    <div class="text-xs font-semibold text-gray-500 uppercase">Saran</div>
                                    <p class="text-sm whitespace-pre-line">{{ $h->saran }}</p>
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-gray-400 italic">Belum ada bacaan</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Gallery image --}}
        @if ($order->images->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold">Gambar Hasil ({{ $order->images->count() }})</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ($order->images as $img)
                            <div class="relative group">
                                @if ($img->is_image)
                                    <a href="{{ route('rad.image.view', $img) }}" target="_blank" class="block">
                                        <img src="{{ route('rad.image.view', $img) }}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                    </a>
                                @else
                                    <a href="{{ route('rad.image.view', $img) }}" target="_blank"
                                       class="block h-32 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500">
                                        <div class="text-center">
                                            <div class="text-3xl">📄</div>
                                            <div class="text-xs mt-1">{{ strtoupper(pathinfo($img->path, PATHINFO_EXTENSION)) }}</div>
                                        </div>
                                    </a>
                                @endif
                                <div class="mt-1 text-xs">
                                    <div class="truncate">{{ $img->label ?: basename($img->path) }}</div>
                                    <div class="text-gray-400">{{ $img->size_formatted }}</div>
                                </div>
                                @if ($order->status->value !== 'SELESAI')
                                    @can('rad.execute')
                                        <form method="POST" action="{{ route('rad.image.delete', $img) }}" class="absolute top-1 right-1">
                                            @csrf @method('DELETE')
                                            <button class="bg-red-600 text-white rounded w-6 h-6 text-xs opacity-0 group-hover:opacity-100"
                                                    onclick="return confirm('Hapus image ini?')">✕</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Kondisi teknis --}}
        @if ($order->kondisi_teknis)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-sm">Kondisi Teknis Pemeriksaan</h3></div>
                <div class="card-body text-sm whitespace-pre-line">{{ $order->kondisi_teknis }}</div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien</h3></div>
            <div class="card-body text-sm">
                <div class="font-semibold">{{ $order->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500">{{ $order->kunjungan->pasien->no_rm }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $order->kunjungan->pasien->jenis_kelamin->label() }} · {{ $order->kunjungan->pasien->umur }} thn
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Timeline</h3></div>
            <div class="card-body text-xs space-y-2">
                <div><span class="text-gray-500">Diorder:</span> {{ $order->tgl_order->format('d M H:i') }}</div>
                @if ($order->eksekusi_at)
                    <div><span class="text-gray-500">Eksekusi:</span> {{ $order->eksekusi_at->format('d M H:i') }}
                        @if($order->radiografer)<div class="text-gray-400">oleh {{ $order->radiografer->name }}</div>@endif
                    </div>
                @endif
                @if ($order->validated_at)
                    <div><span class="text-gray-500">Validasi:</span> {{ $order->validated_at->format('d M H:i') }}
                        @if($order->radiolog)<div class="text-gray-400">oleh {{ $order->radiolog->name }}</div>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
