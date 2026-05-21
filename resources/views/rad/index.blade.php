@extends('layouts.app')

@section('title', 'Radiologi')
@section('page-header', true)
@section('page-title', 'Worklist Radiologi')
@section('page-subtitle', 'Order pemeriksaan radiologi & pencitraan')

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="select w-auto" onchange="this.form.submit()">
                <option value="">Semua status</option>
                @foreach (\App\Enums\StatusOrderRadiologi::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <select name="prioritas" class="select w-auto" onchange="this.form.submit()">
                <option value="">Semua prioritas</option>
                <option value="CITO" @selected(request('prioritas') === 'CITO')>CITO</option>
                <option value="RUTIN" @selected(request('prioritas') === 'RUTIN')>Rutin</option>
            </select>
            @if (request()->hasAny(['status', 'prioritas']))
                <a href="{{ route('rad.index') }}" class="btn-secondary">Reset</a>
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
                    <th>Pemeriksaan</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="{{ $o->prioritas->value === 'CITO' ? 'bg-red-50' : '' }}">
                        <td class="font-mono text-xs">
                            <a href="{{ route('rad.show', $o) }}" class="text-primary-600 hover:underline">{{ $o->no_order }}</a>
                        </td>
                        <td class="text-xs">{{ $o->tgl_order->format('d M H:i') }}</td>
                        <td>
                            <div class="font-medium">{{ $o->kunjungan->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $o->kunjungan->pasien->no_rm }}</div>
                            @if ($o->hamil)<span class="badge badge-yellow text-xs">⚠ Hamil</span>@endif
                        </td>
                        <td class="text-sm">{{ $o->dokter->nama_lengkap }}</td>
                        <td class="text-xs">
                            {{ $o->details->pluck('pemeriksaan.nama')->take(2)->implode(', ') }}
                            @if ($o->details->count() > 2)<span class="text-gray-400">+{{ $o->details->count() - 2 }}</span>@endif
                        </td>
                        <td>
                            @if ($o->prioritas->value === 'CITO')
                                <span class="badge badge-red font-bold">⚡ CITO</span>
                            @else
                                <span class="badge badge-gray">Rutin</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $o->status->color() }}">{{ $o->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('rad.show', $o) }}" class="btn-secondary btn-sm">Proses</a>
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
