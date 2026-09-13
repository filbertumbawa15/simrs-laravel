@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-header', true)
@section('page-title', 'Audit Log')
@section('page-subtitle', 'Riwayat perubahan data (Permenkes 269/2008 — retensi 5 tahun)')

@section('content')

<div x-data="dataTable({
        endpoint: '{{ route('audit-log.data') }}',
        storageKey: 'sihrs.audit-log',
        defaults: { sortBy: 'created_at', sortOrder: 'desc', perPage: 30 },
        filters: { causer_id: '', subject_type: '', event: '', dari: '', sampai: '' },
     })"
     x-init="load()">

    {{-- Filter section --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="label text-xs">User</label>
                    <select x-model="filters.causer_id" @change="onFilter()" class="select">
                        <option value="">— Semua —</option>
                        @foreach ($causers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label text-xs">Tipe Data</label>
                    <select x-model="filters.subject_type" @change="onFilter()" class="select">
                        <option value="">— Semua —</option>
                        @foreach ($subjectTypes as $t)
                            <option value="{{ $t }}">{{ class_basename($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label text-xs">Aksi</label>
                    <select x-model="filters.event" @change="onFilter()" class="select">
                        <option value="">— Semua —</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
                <div>
                    <label class="label text-xs">Dari</label>
                    <input type="date" x-model="filters.dari" @change="onFilter()" class="input">
                </div>
                <div>
                    <label class="label text-xs">Sampai</label>
                    <input type="date" x-model="filters.sampai" @change="onFilter()" class="input">
                </div>
                <div class="flex items-end">
                    <button @click="reset()" class="btn-secondary">Reset</button>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                <span x-text="`Menampilkan ${pagination.from ?? 0}–${pagination.to ?? 0} dari ${pagination.total ?? 0} entri`"></span>
                <span x-show="loading" class="ml-2 text-gray-400">⏳ memuat…</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th @click="toggleSort('created_at')" class="cursor-pointer select-none hover:bg-gray-100 text-xs">
                                Waktu <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('created_at')" :class="sortBy==='created_at' ? 'text-primary-700 font-bold' : ''"></span>
                            </th>
                            <th class="text-xs">User</th>
                            <th @click="toggleSort('event')" class="cursor-pointer select-none hover:bg-gray-100 text-xs">
                                Aksi <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('event')" :class="sortBy==='event' ? 'text-primary-700 font-bold' : ''"></span>
                            </th>
                            <th @click="toggleSort('subject_type')" class="cursor-pointer select-none hover:bg-gray-100 text-xs">
                                Tipe <span class="ml-1 text-gray-400 text-xs" x-text="sortIcon('subject_type')" :class="sortBy==='subject_type' ? 'text-primary-700 font-bold' : ''"></span>
                            </th>
                            <th class="text-xs">Deskripsi</th>
                            <th class="text-xs">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="a in data" :key="a.id">
                            <tr class="align-top">
                                <td class="text-xs text-gray-600 whitespace-nowrap">
                                    <span x-text="a.created_date"></span><br>
                                    <span class="text-gray-400" x-text="a.created_time"></span>
                                </td>
                                <td class="text-sm" x-text="a.causer"></td>
                                <td>
                                    <span class="inline-block text-xs px-2 py-0.5 rounded"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': a.event === 'created',
                                              'bg-blue-100 text-blue-700': a.event === 'updated',
                                              'bg-red-100 text-red-700': a.event === 'deleted',
                                              'bg-gray-100 text-gray-700': !['created','updated','deleted'].includes(a.event),
                                          }"
                                          x-text="a.event ? (a.event[0].toUpperCase() + a.event.slice(1)) : '-'"></span>
                                </td>
                                <td class="text-xs">
                                    <span x-text="a.subject_type"></span>
                                    <span class="text-gray-400 block font-mono" x-text="a.subject_id_short"></span>
                                </td>
                                <td class="text-sm" x-text="a.description"></td>
                                <td class="text-xs">
                                    <template x-if="a.properties && Object.keys(a.properties).length > 0">
                                        <details>
                                            <summary class="cursor-pointer text-gray-600">Lihat detail</summary>
                                            <pre class="mt-1 text-[10px] bg-gray-50 p-2 rounded overflow-auto max-w-xs" x-text="JSON.stringify(a.properties, null, 2)"></pre>
                                        </details>
                                    </template>
                                    <template x-if="!a.properties || Object.keys(a.properties).length === 0">
                                        <span class="text-gray-400">—</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!loading && data.length === 0">
                            <td colspan="6" class="text-center py-8 text-gray-400">Tidak ada aktivitas yang cocok dengan filter.</td>
                        </tr>
                        <tr x-show="loading && data.length === 0">
                            <td colspan="6" class="text-center py-8 text-gray-400">Memuat data…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @include('partials.datatable-pagination')
    </div>
</div>

@endsection
