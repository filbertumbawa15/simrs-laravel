@extends('layouts.app')

@section('title', 'Bed Management')
@section('page-header', true)
@section('page-title', 'Bed Management Board')
@section('page-subtitle', 'Realtime status semua tempat tidur')

@section('content')

{{-- Statistik occupancy --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <div class="card">
        <div class="card-body">
            <div class="text-xs text-gray-500">Total TT</div>
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="card border-l-4 border-emerald-500">
        <div class="card-body">
            <div class="text-xs text-gray-500">Tersedia</div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['tersedia'] }}</div>
        </div>
    </div>
    <div class="card border-l-4 border-red-500">
        <div class="card-body">
            <div class="text-xs text-gray-500">Terisi</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['terisi'] }}</div>
        </div>
    </div>
    <div class="card border-l-4 border-orange-500">
        <div class="card-body">
            <div class="text-xs text-gray-500">Perlu Dibersihkan</div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats['kotor'] }}</div>
        </div>
    </div>
    <div class="card border-l-4 border-primary-600">
        <div class="card-body">
            <div class="text-xs text-gray-500">Occupancy Rate</div>
            <div class="text-2xl font-bold text-primary-600">{{ $stats['occupancy_rate'] }}%</div>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="flex flex-wrap gap-3 mb-4 text-xs">
    @foreach ([
        'TERSEDIA' => ['Tersedia', 'bg-emerald-100 border-emerald-500 text-emerald-800'],
        'TERISI' => ['Terisi', 'bg-red-100 border-red-500 text-red-800'],
        'KOTOR' => ['Perlu Dibersihkan', 'bg-orange-100 border-orange-500 text-orange-800'],
        'MAINTENANCE' => ['Maintenance', 'bg-gray-200 border-gray-500 text-gray-800'],
        'RESERVED' => ['Reserved', 'bg-yellow-100 border-yellow-500 text-yellow-800'],
    ] as $val => [$label, $class])
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 border-2 rounded {{ $class }}"></div>
            <span>{{ $label }}</span>
        </div>
    @endforeach
</div>

{{-- Grid per kelas --}}
@foreach ($kelas as $kls)
    <div class="card mb-6">
        <div class="card-header">
            <div>
                <h3 class="font-semibold">Kelas {{ $kls->nama }}</h3>
                <p class="text-xs text-gray-500">Rp {{ number_format($kls->tarif_per_hari, 0, ',', '.') }}/hari</p>
            </div>
            <div class="text-sm text-gray-600">
                {{ $kls->kamar->where('status.value', 'TERSEDIA')->count() }} / {{ $kls->kamar->count() }} tersedia
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                @foreach ($kls->kamar as $k)
                    @php
                        $bgClass = match ($k->status->value) {
                            'TERSEDIA'    => 'bg-emerald-100 border-emerald-500 hover:bg-emerald-200',
                            'TERISI'      => 'bg-red-100 border-red-500',
                            'KOTOR'       => 'bg-orange-100 border-orange-500',
                            'MAINTENANCE' => 'bg-gray-200 border-gray-500',
                            'RESERVED'    => 'bg-yellow-100 border-yellow-500',
                        };
                        $pasien = $k->kamarInap->first()?->rawatInap?->kunjungan?->pasien;
                    @endphp
                    <div class="relative group">
                        <div class="border-2 rounded-lg p-2 text-center cursor-pointer {{ $bgClass }} min-h-[70px] flex flex-col items-center justify-center">
                            <div class="font-bold text-sm">{{ $k->no_kamar }}</div>
                            @if ($pasien)
                                <div class="text-[10px] truncate w-full mt-1" title="{{ $pasien->nama }}">
                                    {{ Str::limit($pasien->nama, 12) }}
                                </div>
                            @endif
                        </div>
                        {{-- Tooltip --}}
                        @if ($k->status->value !== 'TERSEDIA' || $pasien)
                            <div class="absolute z-10 invisible group-hover:visible bg-gray-900 text-white text-xs rounded p-2 mt-1 w-48 left-1/2 -translate-x-1/2 shadow-lg">
                                <div class="font-semibold">{{ $k->no_kamar }} — {{ $k->status->label() }}</div>
                                @if ($pasien)
                                    <div class="mt-1 border-t border-gray-700 pt-1">
                                        <div>{{ $pasien->nama }}</div>
                                        <div class="text-gray-300">{{ $pasien->no_rm }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach

@endsection
