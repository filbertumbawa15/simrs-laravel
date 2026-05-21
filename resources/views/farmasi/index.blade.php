@extends('layouts.app')

@section('title', 'Farmasi')
@section('page-header', true)
@section('page-title', 'Worklist Farmasi')
@section('page-subtitle', 'Resep yang perlu diproses')

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <select name="status" class="select" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="BARU" @selected(request('status') === 'BARU')>Baru (perlu verifikasi)</option>
                <option value="DIVERIFIKASI" @selected(request('status') === 'DIVERIFIKASI')>Diverifikasi (siap diserahkan)</option>
                <option value="DISERAHKAN" @selected(request('status') === 'DISERAHKAN')>Sudah diserahkan</option>
            </select>
            @if (request('status'))<a href="{{ route('resep.index') }}" class="btn-secondary">Reset</a>@endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Resep</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Item</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resep as $r)
                    <tr>
                        <td class="font-mono text-xs">
                            <a href="{{ route('resep.show', $r) }}" class="text-primary-600 hover:underline">{{ $r->no_resep }}</a>
                        </td>
                        <td class="text-xs">{{ $r->tgl_resep->format('d M Y H:i') }}</td>
                        <td>
                            <div class="font-medium">{{ $r->kunjungan->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $r->kunjungan->pasien->no_rm }}</div>
                        </td>
                        <td class="text-sm">{{ $r->dokter->nama_lengkap }}</td>
                        <td class="text-sm">{{ $r->details->count() }} item</td>
                        <td>
                            @php
                                $color = match($r->status) {
                                    'BARU' => 'yellow',
                                    'DIVERIFIKASI' => 'blue',
                                    'DISERAHKAN' => 'green',
                                    default => 'gray',
                                };
                            @endphp
                            <span class="badge badge-{{ $color }}">{{ $r->status }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('resep.show', $r) }}" class="btn-secondary btn-sm">Proses</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-400">Tidak ada resep</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($resep->hasPages())<div class="card-footer">{{ $resep->links() }}</div>@endif
</div>

@endsection
