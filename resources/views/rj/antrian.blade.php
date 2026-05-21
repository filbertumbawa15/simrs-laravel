@extends('layouts.app')

@section('title', 'Antrian Poli')
@section('page-header', true)
@section('page-title', 'Antrian Rawat Jalan')
@section('page-subtitle', 'Hari ini, '.now()->translatedFormat('l, d F Y'))

@section('content')

{{-- Filter poli --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="label text-xs">Filter Poli</label>
                <select name="poli_id" class="select" onchange="this.form.submit()">
                    <option value="">— Semua poli —</option>
                    @foreach ($poli as $p)
                        <option value="{{ $p->id }}" @selected($poliId === $p->id)>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            @if ($poliId)
                <a href="{{ route('rj.antrian') }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

@if ($antrian->isEmpty())
    <div class="card">
        <div class="card-body text-center py-12 text-gray-400">
            Tidak ada antrian aktif hari ini
        </div>
    </div>
@else
    {{-- Group per poli --}}
    @foreach ($antrian as $poliId => $items)
        @php $firstPoli = $items->first()->poli; @endphp
        <div class="card mb-6">
            <div class="card-header">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $firstPoli->nama }}</h3>
                    <p class="text-xs text-gray-500">{{ $firstPoli->lokasi }}</p>
                </div>
                <span class="badge badge-teal">{{ count($items) }} pasien</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-16">No.</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Status</th>
                            <th>Waktu Daftar</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $rj)
                            <tr class="{{ $rj->waktu_mulai_periksa ? 'bg-amber-50' : '' }}">
                                <td>
                                    <div class="text-2xl font-bold text-primary-700">{{ $rj->no_antrian }}</div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $rj->kunjungan->pasien->nama }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $rj->kunjungan->pasien->no_rm }} •
                                        {{ $rj->kunjungan->pasien->jenis_kelamin->label() }} •
                                        {{ $rj->kunjungan->pasien->umur }} thn
                                    </div>
                                </td>
                                <td class="text-sm">{{ $rj->dokter->nama_lengkap }}</td>
                                <td>
                                    @if ($rj->waktu_mulai_periksa)
                                        <span class="badge badge-yellow">Sedang diperiksa</span>
                                    @elseif ($rj->waktu_panggilan)
                                        <span class="badge badge-blue">Sudah dipanggil</span>
                                    @else
                                        <span class="badge badge-gray">Menunggu</span>
                                    @endif
                                </td>
                                <td class="text-xs text-gray-500">{{ $rj->created_at->format('H:i') }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (! $rj->waktu_panggilan)
                                            <form method="POST" action="{{ route('rj.panggil', $rj) }}" class="inline">
                                                @csrf
                                                <button class="btn-warning btn-sm">📢 Panggil</button>
                                            </form>
                                        @endif
                                        @can('rj.examine')
                                            <a href="{{ route('rj.periksa', $rj) }}" class="btn-primary btn-sm">
                                                {{ $rj->waktu_mulai_periksa ? 'Lanjut Periksa' : 'Mulai Periksa' }}
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif

@endsection
