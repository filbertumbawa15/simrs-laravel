@extends('layouts.app')

@section('title', 'Laboratorium')
@section('page-header', true)
@section('page-title', 'Worklist Laboratorium')
@section('page-subtitle', 'Order pemeriksaan dari poli/IGD/ranap')

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="select w-auto">
                <option value="">Semua status</option>
                <option value="DIORDER" @selected(request('status') === 'DIORDER')>Diorder (perlu sampling)</option>
                <option value="SAMPEL_DIAMBIL" @selected(request('status') === 'SAMPEL_DIAMBIL')>Sampel Diambil</option>
                <option value="DIPROSES" @selected(request('status') === 'DIPROSES')>Sedang Diproses</option>
                <option value="VALIDASI" @selected(request('status') === 'VALIDASI')>Menunggu Validasi</option>
                <option value="SELESAI" @selected(request('status') === 'SELESAI')>Selesai</option>
            </select>
            <select name="prioritas" class="select w-auto">
                <option value="">Semua prioritas</option>
                <option value="CITO" @selected(request('prioritas') === 'CITO')>CITO</option>
                <option value="RUTIN" @selected(request('prioritas') === 'RUTIN')>Rutin</option>
            </select>
            <button class="btn-primary">Filter</button>
            @if (request()->hasAny(['status', 'prioritas']))
                <a href="{{ route('lab.index') }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Dokter Perujuk</th>
                    <th>Parameter</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="{{ $o->prioritas->value === 'CITO' ? 'bg-red-50' : '' }}">
                        <td class="font-mono text-xs">
                            <a href="{{ route('lab.show', $o) }}" class="text-primary-600 hover:underline">{{ $o->no_order }}</a>
                        </td>
                        <td class="text-xs">{{ $o->tgl_order->format('d M H:i') }}</td>
                        <td>
                            <div class="font-medium">{{ $o->kunjungan->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $o->kunjungan->pasien->no_rm }}</div>
                        </td>
                        <td class="text-sm">{{ $o->dokter->nama_lengkap }}</td>
                        <td class="text-xs">{{ $o->details->count() }} parameter</td>
                        <td>
                            @if ($o->prioritas->value === 'CITO')
                                <span class="badge badge-red font-bold">⚡ CITO</span>
                            @else
                                <span class="badge badge-gray">Rutin</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $color = match($o->status->value) {
                                    'DIORDER' => 'yellow', 'SAMPEL_DIAMBIL' => 'blue',
                                    'DIPROSES' => 'purple', 'VALIDASI' => 'yellow',
                                    'SELESAI' => 'green', 'BATAL' => 'red',
                                };
                            @endphp
                            <span class="badge badge-{{ $color }}">{{ $o->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('lab.show', $o) }}" class="btn-secondary btn-sm">Proses</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-12 text-gray-400">Tidak ada order</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())<div class="card-footer">{{ $orders->links() }}</div>@endif
</div>

@endsection
