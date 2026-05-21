@extends('layouts.app')

@section('title', 'Kunjungan')
@section('page-header', true)
@section('page-title', 'Daftar Kunjungan')
@section('page-subtitle', 'Semua episode pelayanan pasien')

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="label text-xs">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="input">
            </div>
            <div>
                <label class="label text-xs">Tipe</label>
                <select name="tipe" class="select">
                    <option value="">Semua tipe</option>
                    <option value="RJ" @selected(request('tipe')==='RJ' )>Rawat Jalan</option>
                    <option value="RI" @selected(request('tipe')==='RI' )>Rawat Inap</option>
                    <option value="IGD" @selected(request('tipe')==='IGD' )>IGD</option>
                </select>
            </div>
            <div>
                <label class="label text-xs">Status</label>
                <select name="status" class="select">
                    <option value="">Semua status</option>
                    @foreach (\App\Enums\StatusKunjungan::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="btn-primary">Filter</button>
                <a href="{{ route('kunjungan.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Kunjungan</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Tipe</th>
                    <th>Poli / Tujuan</th>
                    <th>Penjamin</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kunjungan as $kj)
                <tr>
                    <td class="font-mono text-xs">
                        <a href="{{ route('kunjungan.show', $kj) }}" class="text-primary-600 hover:underline">
                            {{ $kj->no_kunjungan }}
                        </a>
                    </td>
                    <td>{{ $kj->tgl_masuk->format('d M Y H:i') }}</td>
                    <td>
                        <div class="font-medium">{{ $kj->pasien->nama }}</div>
                        <div class="text-xs text-gray-500">{{ $kj->pasien->no_rm }}</div>
                    </td>
                    <td><span class="badge {{ $kj->tipe->badge() }}">{{ $kj->tipe->label() }}</span></td>
                    <td class="text-xs">{{ $kj->rawatJalan?->poli?->nama ?? '—' }}</td>
                    <td><span class="badge badge-gray text-xs">{{ $kj->penjamin->label() }}</span></td>
                    <td><span class="badge badge-yellow">{{ $kj->status->label() }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('kunjungan.show', $kj) }}" class="btn-secondary btn-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">Tidak ada data kunjungan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($kunjungan->hasPages())
    <div class="card-footer">{{ $kunjungan->links() }}</div>
    @endif
</div>

@endsection