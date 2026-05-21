@extends('layouts.app')

@section('title', 'Pasien')
@section('page-header', true)
@section('page-title', 'Daftar Pasien')
@section('page-subtitle', 'Master data pasien rumah sakit')

@section('page-actions')
@can('pasien.create')
<a href="{{ route('pasien.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
    </svg>
    Pasien Baru
</a>
@endcan
@endsection

@section('content')

<div class="card">
    {{-- Search bar --}}
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Cari nama, no. RM, NIK, atau no. telepon…"
                class="input flex-1">
            <button class="btn-primary">Cari</button>
            @if (request('q'))
            <a href="{{ route('pasien.index') }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. RM</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>L/P</th>
                    <th>Umur</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pasien as $p)
                <tr>
                    <td class="font-mono">{{ $p->no_rm }}</td>
                    <td>
                        <div class="font-medium text-gray-900">{{ $p->nama }}</div>
                        @if ($p->tempat_lahir)
                        <div class="text-xs text-gray-500">{{ $p->tempat_lahir }}, {{ $p->tgl_lahir->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td class="font-mono text-xs">{{ $p->nik ?: '—' }}</td>
                    <td>{{ $p->jenis_kelamin->label() }}</td>
                    <td>{{ $p->umur }} thn</td>
                    <td>{{ $p->telp ?: '—' }}</td>
                    <td class="max-w-xs truncate text-xs text-gray-600">{{ $p->alamat }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pasien.show', $p) }}" class="text-primary-600 hover:underline text-xs">
                                Detail
                            </a>
                            @can('kunjungan.create')
                            <a href="{{ route('kunjungan.create', ['pasien_id' => $p->id]) }}"
                                class="btn-primary btn-sm">
                                Daftar Kunjungan
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        @if (request('q'))
                        Tidak ada pasien dengan kata kunci "<strong>{{ request('q') }}</strong>"
                        @else
                        Belum ada data pasien
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($pasien->hasPages())
    <div class="card-footer">
        {{ $pasien->links() }}
    </div>
    @endif
</div>

@endsection