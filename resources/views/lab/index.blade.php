@extends('layouts.app')

@section('title', 'Laboratorium')
@section('page-header', true)
@section('page-title', 'Worklist Laboratorium')
@section('page-subtitle', 'Order pemeriksaan dari poli/IGD/ranap')

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('lab.data') }}',
        storageKey: 'sihrs.lab',
        defaults: { sortBy: 'tgl_order', sortOrder: 'desc', perPage: 20 },
        filters: { status: '', prioritas: '' },
     })"
     x-init="load()"
     class="card">

    <x-datatable-controls searchPlaceholder="Cari no. order, nama pasien, No. RM…">
        <select x-model="filters.status" @change="onFilter()" class="select w-auto">
            <option value="">Semua status</option>
            <option value="DIORDER">Diorder (perlu sampling)</option>
            <option value="SAMPEL_DIAMBIL">Sampel Diambil</option>
            <option value="DIPROSES">Sedang Diproses</option>
            <option value="VALIDASI">Menunggu Validasi</option>
            <option value="SELESAI">Selesai</option>
        </select>
        <select x-model="filters.prioritas" @change="onFilter()" class="select w-auto">
            <option value="">Semua prioritas</option>
            <option value="CITO">CITO</option>
            <option value="RUTIN">Rutin</option>
        </select>
    </x-datatable-controls>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th @click="toggleSort('no_order')" class="cursor-pointer select-none hover:bg-gray-100">
                        No. Order <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('no_order')" :class="sortBy==='no_order' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('tgl_order')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tanggal <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_order')" :class="sortBy==='tgl_order' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Parameter</th>
                    <th @click="toggleSort('prioritas')" class="cursor-pointer select-none hover:bg-gray-100">
                        Prioritas <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('prioritas')" :class="sortBy==='prioritas' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('status')" class="cursor-pointer select-none hover:bg-gray-100">
                        Status <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('status')" :class="sortBy==='status' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="o in data" :key="o.id">
                    <tr :class="o.prioritas === 'CITO' ? 'bg-red-50' : ''">
                        <td class="font-mono text-xs">
                            <a :href="o.url" class="text-primary-600 hover:underline" x-text="o.no_order"></a>
                        </td>
                        <td class="text-xs" x-text="o.tgl_order"></td>
                        <td>
                            <div class="font-medium" x-text="o.pasien.nama"></div>
                            <div class="text-xs text-gray-500" x-text="o.pasien.no_rm"></div>
                        </td>
                        <td class="text-sm" x-text="o.dokter"></td>
                        <td class="text-xs" x-text="`${o.jumlah_parameter} parameter`"></td>
                        <td>
                            <span x-show="o.prioritas === 'CITO'" class="badge badge-red font-bold text-xs">⚡ CITO</span>
                            <span x-show="o.prioritas !== 'CITO'" class="badge badge-gray text-xs" x-text="o.prioritas_label"></span>
                        </td>
                        <td>
                            <span class="badge text-xs"
                                  :class="{
                                      'badge-yellow': ['DIORDER','VALIDASI'].includes(o.status),
                                      'badge-blue': o.status === 'SAMPEL_DIAMBIL',
                                      'badge-purple': o.status === 'DIPROSES',
                                      'badge-green': o.status === 'SELESAI',
                                      'badge-red': o.status === 'BATAL',
                                  }"
                                  x-text="o.status_label"></span>
                        </td>
                        <td class="text-right">
                            <a :href="o.url" class="btn-secondary btn-sm">Proses</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">Tidak ada order lab.</td>
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
