@props(['searchPlaceholder' => 'Cari…', 'extraFilters' => null])

{{--
    Reusable header controls untuk Alpine dataTable.
    Butuh: q, perPage, onSearch(), onFilter(), reset(), pagination, loading dari x-data.
--}}
<div class="card-body border-b border-gray-200">
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px]">
            <input type="text"
                   x-model="q"
                   @input="onSearch()"
                   placeholder="{{ $searchPlaceholder }}"
                   class="input pr-10">
            <span x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">⏳</span>
        </div>

        {{-- Extra filter (select box) — passed as slot --}}
        {{ $slot }}

        <select x-model="perPage" @change="onFilter()" class="select w-auto">
            <option value="10">10 / halaman</option>
            <option value="15">15 / halaman</option>
            <option value="25">25 / halaman</option>
            <option value="50">50 / halaman</option>
            <option value="100">100 / halaman</option>
        </select>

        <button type="button" @click="reset()" class="btn-secondary btn-sm">
            Reset
        </button>
    </div>

    <div class="mt-2 text-xs text-gray-500">
        <span x-text="`Menampilkan ${pagination.from ?? 0}–${pagination.to ?? 0} dari ${pagination.total ?? 0} data`"></span>
        <span x-show="q" x-text="` · kata kunci: '${q}'`" class="text-primary-700 font-medium"></span>
    </div>
</div>
