@extends('layouts.app')

@section('title', 'Billing')
@section('page-header', true)
@section('page-title', 'Daftar Tagihan')
@section('page-subtitle', 'Manajemen pembayaran & klaim')

@section('page-actions')
@if(auth()->user()->hasAnyRole(['MANAGER', 'DIREKSI', 'AUDITOR', 'KASIR_SUPERVISOR', 'SUPER_ADMIN']))
    <a href="{{ route('export.tagihan') }}" class="btn-secondary">📊 Export Excel</a>
@endif
@endsection

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('billing.data') }}',
        storageKey: 'sihrs.billing',
        defaults: { sortBy: 'tgl_tagihan', sortOrder: 'desc', perPage: 20 },
        filters: { status: '', tanggal: '' },
     })"
     x-init="load()"
     class="card">

    <x-datatable-controls searchPlaceholder="Cari no. tagihan, nama pasien, No. RM…">
        <input type="date" x-model="filters.tanggal" @change="onFilter()" class="input w-auto">
        <select x-model="filters.status" @change="onFilter()" class="select w-auto">
            <option value="">Semua status</option>
            @foreach (\App\Enums\StatusTagihan::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </x-datatable-controls>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th @click="toggleSort('no_tagihan')" class="cursor-pointer select-none hover:bg-gray-100">
                        No. Tagihan <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('no_tagihan')" :class="sortBy==='no_tagihan' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('tgl_tagihan')" class="cursor-pointer select-none hover:bg-gray-100">
                        Tanggal <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('tgl_tagihan')" :class="sortBy==='tgl_tagihan' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th>Pasien</th>
                    <th @click="toggleSort('total')" class="text-right cursor-pointer select-none hover:bg-gray-100">
                        Total <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('total')" :class="sortBy==='total' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th class="text-right">Dibayar</th>
                    <th @click="toggleSort('sisa')" class="text-right cursor-pointer select-none hover:bg-gray-100">
                        Sisa <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('sisa')" :class="sortBy==='sisa' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th @click="toggleSort('status')" class="cursor-pointer select-none hover:bg-gray-100">
                        Status <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('status')" :class="sortBy==='status' ? 'text-primary-700 font-bold' : ''"></span>
                    </th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="t in data" :key="t.id">
                    <tr>
                        <td class="font-mono text-xs">
                            <a :href="t.urls.show" class="text-primary-600 hover:underline" x-text="t.no_tagihan"></a>
                        </td>
                        <td class="text-xs" x-text="t.tgl_tagihan"></td>
                        <td>
                            <div class="font-medium" x-text="t.pasien.nama"></div>
                            <div class="text-xs text-gray-500" x-text="t.pasien.no_rm"></div>
                        </td>
                        <td class="text-right font-semibold" x-text="'Rp ' + t.total.toLocaleString('id-ID')"></td>
                        <td class="text-right text-green-700" x-text="'Rp ' + t.dibayar.toLocaleString('id-ID')"></td>
                        <td class="text-right text-red-700" x-text="'Rp ' + t.sisa.toLocaleString('id-ID')"></td>
                        <td>
                            <span class="badge text-xs"
                                  :class="{
                                      'badge-gray': t.status === 'DRAFT',
                                      'badge-yellow': t.status === 'BELUM_LUNAS',
                                      'badge-blue': t.status === 'CICILAN',
                                      'badge-green': t.status === 'LUNAS',
                                      'badge-purple': t.status === 'KLAIM',
                                      'badge-red': t.status === 'VOID',
                                  }"
                                  x-text="t.status_label"></span>
                        </td>
                        <td class="text-right">
                            <a :href="t.urls.show" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && data.length === 0">
                    <td colspan="8" class="text-center py-12 text-gray-400">Tidak ada tagihan yang cocok.</td>
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
