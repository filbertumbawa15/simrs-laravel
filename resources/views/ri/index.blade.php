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

<div x-data="dataTable({
        endpoint: '{{ route('ri.data') }}',
        storageKey: 'sihrs.ri',
        defaults: { sortBy: 'tgl_masuk_ri', sortOrder: 'desc', perPage: 20 },
        filters: { status: 'aktif' },
     })"
     x-init="load()"
     class="card">

    <x-datatable-controls searchPlaceholder="Cari nama pasien atau No. RM…">
        <select x-model="filters.status" @change="onFilter()" class="select w-auto">
            <option value="aktif">Sedang dirawat</option>
            <option value="pulang">Sudah pulang</option>
            <option value="">Semua</option>
        </select>
    </x-datatable-controls>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Pasien</th>
                    <th @click="toggleSort('tgl_masuk_ri')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tgl Masuk <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_masuk_ri')" :class="sortBy==='tgl_masuk_ri' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Kamar</th>
                    <th>DPJP</th>
                    <th @click="toggleSort('tgl_pulang')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tgl Pulang <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_pulang')" :class="sortBy==='tgl_pulang' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="r in data" :key="r.id">
                    <tr>
                        <td>
                            <div class="font-medium" x-text="r.pasien.nama"></div>
                            <div class="text-xs text-gray-500" x-text="`${r.pasien.no_rm} · ${r.pasien.umur} thn`"></div>
                        </td>
                        <td class="text-xs" x-text="r.tgl_masuk_ri"></td>
                        <td>
                            <template x-if="r.kamar">
                                <div>
                                    <div class="font-medium" x-text="r.kamar"></div>
                                    <div class="text-xs text-gray-500" x-text="r.kelas"></div>
                                </div>
                            </template>
                            <span x-show="!r.kamar" class="text-gray-400 italic">—</span>
                        </td>
                        <td class="text-sm" x-text="r.dpjp"></td>
                        <td class="text-xs" x-text="r.tgl_pulang || '—'"></td>
                        <td>
                            <span x-show="!r.tgl_pulang" class="badge badge-purple text-xs">Sedang dirawat</span>
                            <template x-if="r.tgl_pulang">
                                <div>
                                    <span class="badge badge-green text-xs">Pulang</span>
                                    <div class="text-xs text-gray-500 mt-1" x-text="r.cara_pulang"></div>
                                </div>
                            </template>
                        </td>
                        <td class="text-right">
                            <a :href="r.url" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && data.length === 0">
                    <td colspan="7" class="text-center py-12 text-gray-400">Tidak ada data rawat inap.</td>
                </tr>
                <tr x-show="loading && data.length === 0">
                    <td colspan="7" class="text-center py-12 text-gray-400">Memuat data…</td>
                </tr>
            </tbody>
        </table>
    </div>

    @include('partials.datatable-pagination')
</div>

@endsection
