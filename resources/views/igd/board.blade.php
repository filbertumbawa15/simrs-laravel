@extends('layouts.app')

@section('title', 'IGD')
@section('page-header', true)
@section('page-title', 'IGD Board')
@section('page-subtitle', 'Antrian pasien diurutkan prioritas triase')

@section('content')

{{-- Legend kategori triase --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="card border-l-4 border-red-600">
        <div class="card-body">
            <div class="text-xs text-gray-500">🔴 MERAH — Resusitasi</div>
            <div class="text-2xl font-bold text-red-700">{{ optional($grouped->get('MERAH'))->count() ?? 0 }}</div>
            <div class="text-xs text-gray-500">Tangani SEGERA</div>
        </div>
    </div>
    <div class="card border-l-4 border-yellow-500">
        <div class="card-body">
            <div class="text-xs text-gray-500">🟡 KUNING — Emergent</div>
            <div class="text-2xl font-bold text-yellow-700">{{ optional($grouped->get('KUNING'))->count() ?? 0 }}</div>
            <div class="text-xs text-gray-500">&lt; 10 menit</div>
        </div>
    </div>
    <div class="card border-l-4 border-green-600">
        <div class="card-body">
            <div class="text-xs text-gray-500">🟢 HIJAU — Urgent</div>
            <div class="text-2xl font-bold text-green-700">{{ optional($grouped->get('HIJAU'))->count() ?? 0 }}</div>
            <div class="text-xs text-gray-500">&lt; 30 menit</div>
        </div>
    </div>
    <div class="card border-l-4 border-gray-700">
        <div class="card-body">
            <div class="text-xs text-gray-500">⚫ HITAM — DOA</div>
            <div class="text-2xl font-bold text-gray-900">{{ optional($grouped->get('HITAM'))->count() ?? 0 }}</div>
            <div class="text-xs text-gray-500">Death on Arrival</div>
        </div>
    </div>
</div>

{{-- Belum triase --}}
@php $belumTriase = $grouped->get('BELUM_TRIASE'); @endphp
@if ($belumTriase && $belumTriase->isNotEmpty())
    <div class="alert alert-warning">
        <strong>⚠ {{ $belumTriase->count() }} pasien belum ditriase</strong> —
        per standar JCI/KARS, triase harus dilakukan dalam 5 menit sejak pasien tiba.
    </div>
    <div class="card mb-6 border-2 border-amber-400">
        <div class="card-header">
            <h3 class="font-semibold text-amber-900">⏰ Belum Ditriase</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tiba</th>
                        <th>Pasien</th>
                        <th>Waiting Time</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($belumTriase as $kj)
                        <tr class="bg-amber-50">
                            <td class="text-xs">{{ $kj->tgl_masuk->format('H:i') }}</td>
                            <td>
                                <div class="font-medium">{{ $kj->pasien->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $kj->pasien->no_rm }}</div>
                            </td>
                            <td class="font-semibold text-red-700">
                                {{ $kj->tgl_masuk->diffForHumans(null, true) }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('igd.triase.form', $kj) }}" class="btn-warning btn-sm">
                                    🚨 Triase Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Per kategori --}}
@foreach (['MERAH', 'KUNING', 'HIJAU', 'HITAM'] as $kat)
    @php $list = $grouped->get($kat); @endphp
    @if ($list && $list->isNotEmpty())
        @php
            [$bgClass, $textClass, $icon, $label] = match ($kat) {
                'MERAH'  => ['bg-red-100 border-red-500', 'text-red-900', '🔴', 'MERAH — RESUSITASI'],
                'KUNING' => ['bg-yellow-100 border-yellow-500', 'text-yellow-900', '🟡', 'KUNING — EMERGENT'],
                'HIJAU'  => ['bg-green-100 border-green-500', 'text-green-900', '🟢', 'HIJAU — URGENT'],
                'HITAM'  => ['bg-gray-200 border-gray-700', 'text-gray-900', '⚫', 'HITAM — DOA'],
            };
        @endphp

        <div class="card mb-6 border-l-4 {{ $bgClass }}">
            <div class="card-header bg-transparent">
                <h3 class="font-bold {{ $textClass }}">
                    {{ $icon }} {{ $label }}
                </h3>
                <span class="badge badge-gray">{{ $list->count() }} pasien</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tiba</th>
                            <th>Pasien</th>
                            <th>Keluhan Utama</th>
                            <th>Triase Oleh</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list as $kj)
                            <tr>
                                <td class="text-xs">{{ $kj->tgl_masuk->format('d M H:i') }}</td>
                                <td>
                                    <div class="font-medium">{{ $kj->pasien->nama }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $kj->pasien->no_rm }} • {{ $kj->pasien->umur }} thn
                                    </div>
                                </td>
                                <td class="text-sm">{{ Str::limit(optional($kj->triase)->keluhan_utama, 80) }}</td>
                                <td class="text-xs">{{ optional($kj->triase?->petugas)->name }}</td>
                                <td class="text-right">
                                    <a href="{{ route('kunjungan.show', $kj) }}" class="btn-secondary btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endforeach

@endsection
