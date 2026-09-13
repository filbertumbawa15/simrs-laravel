@extends('layouts.app')

@section('title', 'Pasien')
@section('page-header', true)
@section('page-title', 'Daftar Pasien')
@section('page-subtitle', 'Master data pasien rumah sakit')

@section('page-actions')
<div class="flex items-center gap-2">
    @if(auth()->user()->hasAnyRole(['MANAGER', 'DIREKSI', 'AUDITOR', 'SUPER_ADMIN']))
        <a href="{{ route('export.pasien') }}" class="btn-secondary">
            📊 Export Excel
        </a>
    @endif
    @can('pasien.create')
    <a href="{{ route('pasien.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Pasien Baru
    </a>
    @endcan
</div>
@endsection

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('pasien.data') }}',
        storageKey: 'sihrs.pasien',
        defaults: { sortBy: 'created_at', sortOrder: 'desc', perPage: 15 },
     })"
     x-init="load()"
     class="card">

    {{-- Search bar --}}
    <div class="card-body border-b border-gray-200">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text"
                       x-model="q"
                       @input="onSearch()"
                       placeholder="Cari nama, no. RM, NIK, atau no. telepon…"
                       class="input pr-10">
                <span x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                    ⏳
                </span>
            </div>

            <select x-model="perPage" @change="onFilter()" class="select w-auto">
                <option value="10">10 / halaman</option>
                <option value="15">15 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>

            <button type="button" @click="reset()" class="btn-secondary btn-sm"
                    x-show="q || sortBy !== 'created_at' || sortOrder !== 'desc'">
                Reset
            </button>
        </div>

        <div class="mt-2 text-xs text-gray-500">
            <span x-text="`Menampilkan ${pagination.from ?? 0}–${pagination.to ?? 0} dari ${pagination.total ?? 0} pasien`"></span>
            <span x-show="q" x-text="` · kata kunci: '${q}'`" class="text-primary-700 font-medium"></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    @php
                        $cols = [
                            ['key' => 'no_rm', 'label' => 'No. RM'],
                            ['key' => 'nama', 'label' => 'Nama'],
                            ['key' => 'nik', 'label' => 'NIK'],
                            ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                            ['key' => 'tgl_lahir', 'label' => 'Umur'],
                            ['key' => 'telp', 'label' => 'Telepon'],
                            ['key' => null, 'label' => 'Alamat'],
                        ];
                    @endphp
                    @foreach($cols as $c)
                        @if($c['key'])
                            <th @click="toggleSort('{{ $c['key'] }}')"
                                class="cursor-pointer select-none hover:bg-gray-100 transition">
                                {{ $c['label'] }}
                                <span class="ml-1 text-gray-400 text-xs"
                                      x-text="sortIcon('{{ $c['key'] }}')"
                                      :class="sortBy === '{{ $c['key'] }}' ? 'text-primary-700 font-bold' : ''"></span>
                            </th>
                        @else
                            <th>{{ $c['label'] }}</th>
                        @endif
                    @endforeach
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="p in data" :key="p.id">
                    <tr>
                        <td class="font-mono" x-text="p.no_rm"></td>
                        <td>
                            <div class="font-medium text-gray-900" x-text="p.nama"></div>
                            <div class="text-xs text-gray-500"
                                 x-show="p.tempat_lahir"
                                 x-text="`${p.tempat_lahir ?? ''}, ${p.tgl_lahir ?? ''}`"></div>
                        </td>
                        <td class="font-mono text-xs" x-text="p.nik || '—'"></td>
                        <td x-text="p.jenis_kelamin_label"></td>
                        <td x-text="`${p.umur} thn`"></td>
                        <td x-text="p.telp || '—'"></td>
                        <td class="max-w-xs truncate text-xs text-gray-600" x-text="p.alamat"></td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a :href="p.urls.show" class="text-primary-600 hover:underline text-xs">Detail</a>
                                @can('kunjungan.create')
                                    <a :href="p.urls.buat_kunjungan" class="btn-primary btn-sm">Daftar Kunjungan</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Empty state --}}
                <tr x-show="!loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <template x-if="q">
                            <div>
                                Tidak ada pasien dengan kata kunci "<strong x-text="q"></strong>"
                            </div>
                        </template>
                        <template x-if="!q">
                            <div>Belum ada data pasien</div>
                        </template>
                    </td>
                </tr>

                {{-- Loading skeleton --}}
                <tr x-show="loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">Memuat data…</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="card-footer" x-show="pagination.last_page > 1">
        <nav class="flex items-center justify-between">
            <div class="text-xs text-gray-500">
                Halaman <strong x-text="pagination.current_page"></strong> dari <strong x-text="pagination.last_page"></strong>
            </div>
            <div class="flex items-center gap-1">
                <button @click="goToPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                    ‹ Sebelumnya
                </button>
                <template x-for="p in pageRange()" :key="p + '-' + Math.random()">
                    <button @click="typeof p === 'number' && goToPage(p)"
                            :disabled="p === '...'"
                            :class="p === pagination.current_page ? 'bg-primary-600 text-white' : 'hover:bg-gray-100'"
                            class="px-3 py-1 text-sm border border-gray-200 rounded min-w-[36px]"
                            x-text="p"></button>
                </template>
                <button @click="goToPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                    Berikutnya ›
                </button>
            </div>
        </nav>
    </div>
</div>

@endsection
