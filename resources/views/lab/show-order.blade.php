@extends('layouts.app')

@section('title', $order->no_order)
@section('page-header', true)
@section('page-title', 'Order Lab '.$order->no_order)
@section('page-subtitle', $order->kunjungan->pasien->nama.' • '.$order->tgl_order->format('d M Y H:i'))

@section('page-actions')
    @php
        $status = $order->status->value;
    @endphp

    @if ($status === 'DIORDER')
        @can('lab.sampling')
            <form method="POST" action="{{ route('lab.sampling', $order) }}" class="inline">
                @csrf
                <button class="btn-warning">🩸 Sampel Diambil</button>
            </form>
        @endcan
    @elseif ($status === 'SAMPEL_DIAMBIL')
        @can('lab.input_hasil')
            <form method="POST" action="{{ route('lab.proses', $order) }}" class="inline">
                @csrf
                <button class="btn-primary">⚗ Mulai Proses</button>
            </form>
        @endcan
    @elseif ($status === 'DIPROSES')
        @can('lab.input_hasil')
            <a href="{{ route('lab.input', $order) }}" class="btn-primary">📝 Input Hasil</a>
        @endcan
    @elseif ($status === 'VALIDASI')
        @can('lab.validate')
            <form method="POST" action="{{ route('lab.validate', $order) }}" class="inline">
                @csrf
                <button class="btn-success"
                        onclick="return confirm('Validasi hasil? Setelah ini hasil resmi rilis dan dokter perujuk dapat melihatnya.')">
                    ✓ Validasi Hasil
                </button>
            </form>
        @endcan
    @endif
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        {{-- Status & info --}}
        <div class="card">
            <div class="card-body grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Status</div>
                    @php
                        $color = match($status) {
                            'DIORDER' => 'yellow', 'SAMPEL_DIAMBIL' => 'blue',
                            'DIPROSES' => 'purple', 'VALIDASI' => 'yellow',
                            'SELESAI' => 'green', 'BATAL' => 'red',
                        };
                    @endphp
                    <span class="badge badge-{{ $color }}">{{ $order->status->label() }}</span>
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
                    <div class="text-xs text-gray-500">Target TAT</div>
                    <div class="font-medium text-xs">
                        {{ $order->prioritas->targetMinutes() < 60
                            ? $order->prioritas->targetMinutes().' menit'
                            : ($order->prioritas->targetMinutes() / 60).' jam' }}
                    </div>
                </div>
            </div>

            @if ($order->catatan_klinis || $order->diagnosa_kerja)
                <div class="card-footer text-sm space-y-2">
                    @if ($order->diagnosa_kerja)
                        <div><span class="text-xs text-gray-500 font-semibold">Diagnosa Kerja:</span> {{ $order->diagnosa_kerja }}</div>
                    @endif
                    @if ($order->catatan_klinis)
                        <div><span class="text-xs text-gray-500 font-semibold">Catatan Klinis:</span> {{ $order->catatan_klinis }}</div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Parameter & Hasil --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Parameter & Hasil</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th class="text-center">Hasil</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Rujukan</th>
                            <th class="text-center">Flag</th>
                            <th class="text-center">Tarif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->details as $d)
                            @php $h = $order->hasil->firstWhere('parameter_id', $d->parameter_id); @endphp
                            <tr class="{{ $h && $h->flag->isCritical() ? 'bg-red-50' : '' }}">
                                <td>
                                    <div class="font-medium">{{ $d->parameter->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $d->parameter->kategori }}</div>
                                </td>
                                <td class="text-center font-bold {{ $h && $h->flag->isCritical() ? 'text-red-700' : '' }}">
                                    {{ $h?->hasil ?: '—' }}
                                </td>
                                <td class="text-center text-xs">{{ $d->parameter->satuan }}</td>
                                <td class="text-center text-xs text-gray-500">{{ $d->parameter->rujukan_normal }}</td>
                                <td class="text-center">
                                    @if ($h)
                                        <span class="badge badge-{{ $h->flag->color() }} font-bold">{{ $h->flag->value }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="text-center text-xs">Rp {{ number_format($d->tarif, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($order->status->value === 'SELESAI' && $order->validator)
                <div class="card-footer text-xs text-gray-600">
                    ✓ Divalidasi oleh <strong>{{ $order->validator->name }}</strong>
                    pada {{ $order->validated_at->format('d M Y H:i') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien</h3></div>
            <div class="card-body text-sm">
                <div class="font-semibold">{{ $order->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500">{{ $order->kunjungan->pasien->no_rm }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $order->kunjungan->pasien->jenis_kelamin->label() }} • {{ $order->kunjungan->pasien->umur }} thn
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Timeline</h3></div>
            <div class="card-body text-xs space-y-2">
                <div><span class="text-gray-500">Diorder:</span> {{ $order->tgl_order->format('d M Y H:i') }}</div>
                @if ($order->sampling_at)
                    <div><span class="text-gray-500">Sampling:</span> {{ $order->sampling_at->format('d M Y H:i') }}</div>
                @endif
                @if ($order->validated_at)
                    <div><span class="text-gray-500">Validasi:</span> {{ $order->validated_at->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
