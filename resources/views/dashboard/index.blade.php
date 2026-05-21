@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-header', true)
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional hari ini')

@section('content')
{{-- Statistik utama --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    @php
    $cards = [
    [
    'label' => 'Total Pasien',
    'value' => number_format($stats['total_pasien']),
    'sub' => '+'.$stats['pasien_baru_hari_ini'].' baru hari ini',
    'color' => 'blue',
    'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m13-14a4 4 0 11-8 0 4 4 0 018 0z',
    ],
    [
    'label' => 'Rawat Jalan Hari Ini',
    'value' => number_format($stats['rj_aktif']),
    'sub' => 'sedang aktif',
    'color' => 'teal',
    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2',
    ],
    [
    'label' => 'Rawat Inap',
    'value' => $stats['kamar_total'] - $stats['kamar_tersedia'].' / '.$stats['kamar_total'],
    'sub' => $stats['kamar_tersedia'].' kamar tersedia',
    'color' => 'purple',
    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    ],
    [
    'label' => 'Pendapatan Hari Ini',
    'value' => 'Rp '.number_format($stats['pendapatan_hari_ini'], 0, ',', '.'),
    'sub' => 'dari tagihan lunas',
    'color' => 'green',
    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
    ],
    ];

    $colorClass = [
    'blue' => 'bg-blue-500',
    'teal' => 'bg-primary-600',
    'purple' => 'bg-purple-500',
    'green' => 'bg-emerald-500',
    ];
    @endphp

    @foreach ($cards as $card)
    <div class="card">
        <div class="card-body flex items-start justify-between">
            <div>
                <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $card['value'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $card['sub'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $colorClass[$card['color']] }} text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                </svg>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Statistik tipe kunjungan --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="card">
        <div class="card-body">
            <div class="text-xs uppercase text-gray-500 font-semibold tracking-wider mb-2">Kunjungan Hari Ini</div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['rj_aktif'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Rawat Jalan</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['igd_aktif'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">IGD</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['ri_aktif'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Rawat Inap</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert stok obat --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">⚠ Obat Stok Menipis</h3>
            <span class="badge badge-red">{{ count($obatHampirHabis) }}</span>
        </div>
        <div class="card-body">
            @forelse ($obatHampirHabis as $obat)
            <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-100 last:border-0">
                <span class="truncate flex-1 mr-2">{{ $obat->nama }}</span>
                <span class="text-red-600 font-semibold">{{ $obat->total_stok }} / {{ $obat->stok_minimum }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">Semua stok di atas minimum ✓</p>
            @endforelse
        </div>
    </div>

    {{-- Alert obat mendekati expired --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">⏰ Obat Akan Expired</h3>
            <span class="badge badge-yellow">{{ count($obatHampirExp) }}</span>
        </div>
        <div class="card-body">
            @forelse ($obatHampirExp as $stok)
            <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-100 last:border-0">
                <span class="truncate flex-1 mr-2">{{ $stok->obat->nama }}</span>
                <span class="text-amber-600 font-semibold text-xs">{{ $stok->exp_date->format('d M Y') }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">Tidak ada obat yang akan expired dalam 90 hari</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Antrian terbaru --}}
<div class="card">
    <div class="card-header">
        <h3 class="font-semibold text-gray-800">Pendaftaran Terbaru Hari Ini</h3>
        <a href="{{ route('kunjungan.index') }}" class="text-sm text-primary-600 hover:underline">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>No. Kunjungan</th>
                    <th>Pasien</th>
                    <th>Tipe</th>
                    <th>Poli/Tujuan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($antrianHariIni as $kj)
                <tr>
                    <td class="text-gray-500">{{ $kj->tgl_masuk->format('H:i') }}</td>
                    <td class="font-mono text-xs">
                        <a href="{{ route('kunjungan.show', $kj) }}" class="text-primary-600 hover:underline">
                            {{ $kj->no_kunjungan }}
                        </a>
                    </td>
                    <td>
                        <div class="font-medium">{{ $kj->pasien->nama }}</div>
                        <div class="text-xs text-gray-500">{{ $kj->pasien->no_rm }}</div>
                    </td>
                    <td><span class="badge {{ $kj->tipe->badge() }}">{{ $kj->tipe->label() }}</span></td>
                    <td class="text-xs">{{ $kj->rawatJalan?->poli?->nama ?? '—' }}</td>
                    <td><span class="badge badge-yellow">{{ $kj->status->label() }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-400 py-8">Belum ada pendaftaran hari ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection