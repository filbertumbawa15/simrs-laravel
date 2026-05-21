@extends('layouts.app')

@section('title', 'Rawat Inap')
@section('page-header', true)
@section('page-title', 'Daftar Rawat Inap')

@section('page-actions')
    <a href="{{ route('kamar.board') }}" class="btn-secondary">
        🏥 Bed Management Board
    </a>
@endsection

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            @foreach (['aktif' => 'Sedang dirawat', 'pulang' => 'Sudah pulang'] as $val => $label)
                <a href="{{ route('ri.index', ['status' => $val]) }}"
                   class="btn-{{ request('status', 'aktif') === $val ? 'primary' : 'secondary' }} btn-sm">
                    {{ $label }}
                </a>
            @endforeach
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Pasien</th>
                    <th>Tgl Masuk</th>
                    <th>Lama Inap</th>
                    <th>Kamar</th>
                    <th>DPJP</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ri as $r)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $r->kunjungan->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $r->kunjungan->pasien->no_rm }}</div>
                        </td>
                        <td class="text-xs">{{ $r->tgl_masuk_ri->format('d M Y H:i') }}</td>
                        <td class="text-sm font-semibold">{{ $r->lama_inap }} hari</td>
                        <td>
                            @if ($r->kamarAktif)
                                <div class="font-medium">{{ $r->kamarAktif->kamar->no_kamar }}</div>
                                <div class="text-xs text-gray-500">{{ $r->kamarAktif->kamar->kelas->nama }}</div>
                            @else
                                <span class="text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $r->dpjp->nama_lengkap }}</td>
                        <td>
                            @if ($r->tgl_pulang)
                                <span class="badge badge-green">Pulang</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $r->cara_pulang }}</div>
                            @else
                                <span class="badge badge-purple">Sedang dirawat</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('ri.show', $r) }}" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($ri->hasPages())<div class="card-footer">{{ $ri->links() }}</div>@endif
</div>

@endsection
