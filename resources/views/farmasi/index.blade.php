@extends('layouts.app')

@section('title', 'Farmasi')
@section('page-header', true)
@section('page-title', 'Worklist Farmasi')
@section('page-subtitle', 'Resep yang perlu diproses')

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('resep.data') }}',
        storageKey: 'sihrs.resep',
        defaults: { sortBy: 'tgl_resep', sortOrder: 'desc', perPage: 20 },
        filters: { status: '' },
     })"
     x-init="load()"
     class="card">

    <x-datatable-controls searchPlaceholder="Cari no. resep, nama pasien, No. RM…">
        <select x-model="filters.status" @change="onFilter()" class="select w-auto">
            <option value="">Semua status</option>
            <option value="BARU">Baru (perlu verifikasi)</option>
            <option value="DIVERIFIKASI">Diverifikasi (siap diserahkan)</option>
            <option value="DISERAHKAN">Sudah diserahkan</option>
        </select>
    </x-datatable-controls>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th @click="toggleSort('no_resep')" class="cursor-pointer select-none hover:bg-gray-100">
                        No. Resep <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('no_resep')" :class="sortBy==='no_resep' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('tgl_resep')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tanggal <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_resep')" :class="sortBy==='tgl_resep' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Item</th>
                    <th @click="toggleSort('status')" class="cursor-pointer select-none hover:bg-gray-100">
                        Status <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('status')" :class="sortBy==='status' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="r in data" :key="r.id">
                    <tr>
                        <td class="font-mono text-xs">
                            <a :href="r.url" class="text-primary-600 hover:underline" x-text="r.no_resep"></a>
                        </td>
                        <td class="text-xs" x-text="r.tgl_resep"></td>
                        <td>
                            <div class="font-medium" x-text="r.pasien.nama"></div>
                            <div class="text-xs text-gray-500" x-text="r.pasien.no_rm"></div>
                        </td>
                        <td class="text-sm" x-text="r.dokter"></td>
                        <td class="text-sm" x-text="`${r.jumlah_item} item`"></td>
                        <td>
                            <span class="badge text-xs"
                                  :class="{
                                      'badge-yellow': r.status === 'BARU',
                                      'badge-blue': r.status === 'DIVERIFIKASI',
                                      'badge-green': r.status === 'DISERAHKAN',
                                      'badge-gray': !['BARU','DIVERIFIKASI','DISERAHKAN'].includes(r.status),
                                  }"
                                  x-text="r.status"></span>
                        </td>
                        <td class="text-right">
                            <a :href="r.url" class="btn-secondary btn-sm">Proses</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && data.length === 0">
                    <td colspan="7" class="text-center py-12 text-gray-400">Tidak ada resep.</td>
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
