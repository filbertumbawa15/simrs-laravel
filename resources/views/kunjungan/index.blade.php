@extends('layouts.app')

@section('title', 'Kunjungan')
@section('page-header', true)
@section('page-title', 'Daftar Kunjungan')
@section('page-subtitle', 'Semua episode pelayanan pasien')

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('kunjungan.data') }}',
        storageKey: 'sihrs.kunjungan',
        defaults: { sortBy: 'tgl_masuk', sortOrder: 'desc', perPage: 20 },
        filters: { tipe: '', status: '', tanggal: '' },
     })"
     x-init="load()"
     class="card">

    <x-datatable-controls searchPlaceholder="Cari no. kunjungan, nama pasien, No. RM, NIK…">
        <input type="date" x-model="filters.tanggal" @change="onFilter()" class="input w-auto">
        <select x-model="filters.tipe" @change="onFilter()" class="select w-auto">
            <option value="">Semua tipe</option>
            <option value="RJ">Rawat Jalan</option>
            <option value="RI">Rawat Inap</option>
            <option value="IGD">IGD</option>
        </select>
        <select x-model="filters.status" @change="onFilter()" class="select w-auto">
            <option value="">Semua status</option>
            @foreach (\App\Enums\StatusKunjungan::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </x-datatable-controls>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th @click="toggleSort('no_kunjungan')" class="cursor-pointer select-none hover:bg-gray-100">
                        No. Kunjungan <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('no_kunjungan')" :class="sortBy==='no_kunjungan' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('tgl_masuk')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tanggal <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_masuk')" :class="sortBy==='tgl_masuk' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Pasien</th>
                    <th @click="toggleSort('tipe')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tipe <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tipe')" :class="sortBy==='tipe' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Poli / Dokter</th>
                    <th @click="toggleSort('penjamin')" class="cursor-pointer select-none hover:bg-gray-100">
                        Penjamin <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('penjamin')" :class="sortBy==='penjamin' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('status')" class="cursor-pointer select-none hover:bg-gray-100">
                        Status <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('status')" :class="sortBy==='status' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="k in data" :key="k.id">
                    <tr>
                        <td class="font-mono text-xs">
                            <a :href="k.url" class="text-primary-600 hover:underline" x-text="k.no_kunjungan"></a>
                        </td>
                        <td class="text-xs" x-text="k.tgl_masuk"></td>
                        <td>
                            <div class="font-medium" x-text="k.pasien.nama"></div>
                            <div class="text-xs text-gray-500" x-text="`${k.pasien.no_rm} · ${k.pasien.jenis_kelamin} · ${k.pasien.umur} thn`"></div>
                        </td>
                        <td><span class="badge badge-teal text-xs" x-text="k.tipe_label"></span></td>
                        <td class="text-xs">
                            <div x-text="k.poli || '—'"></div>
                            <div class="text-gray-500" x-text="k.dokter || ''"></div>
                        </td>
                        <td><span class="badge badge-gray text-xs" x-text="k.penjamin_label"></span></td>
                        <td><span class="badge badge-yellow text-xs" x-text="k.status_label"></span></td>
                        <td class="text-right">
                            <a :href="k.url" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">Tidak ada data kunjungan yang cocok.</td>
                </tr>
                <tr x-show="loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">Memuat data…</td>
                </tr>
            </tbody>
        </table>
    </div>

    @include('partials.datatable-pagination')
</div>

@endsection
