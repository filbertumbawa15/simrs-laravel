/**
 * Alpine.js reactive data-table component.
 *
 * Fitur:
 * - Fetch data dari JSON endpoint (search / sort / paginate) tanpa reload halaman
 * - URL tetap bersih — history.replaceState() strip query string setelah fetch
 * - State di-persist ke localStorage per halaman (key: storageKey)
 * - Debounce search input (300ms) supaya tidak spam endpoint
 * - Sort by klik header — asc → desc → tidak sort
 *
 * Usage di Blade:
 *   <div x-data="dataTable({
 *       endpoint: '/api/pasien',
 *       storageKey: 'sihrs.pasien',
 *       defaults: { sortBy: 'created_at', sortOrder: 'desc', perPage: 15 },
 *       filters: { status: '', tanggal: '' },
 *   })" x-init="load()">
 *
 * Setelah load(), akses via:
 *   - `state.data` — array rows
 *   - `state.pagination` — { current_page, last_page, total, ... }
 *   - `state.loading` — bool
 *   - `state.q` — search text
 *   - `state.sortBy`, `state.sortOrder`
 *   - `state.filters` — object filter-specific
 */
export default function dataTable(config) {
    const {
        endpoint,
        storageKey = null,
        defaults = {},
        filters: filterDefaults = {},
    } = config;

    // Restore dari localStorage kalau ada
    let saved = {};
    if (storageKey) {
        try { saved = JSON.parse(localStorage.getItem(storageKey) || '{}'); } catch (e) {}
    }

    return {
        // State
        loading: false,
        data: [],
        pagination: { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 },
        q: saved.q ?? '',
        sortBy: saved.sortBy ?? defaults.sortBy ?? null,
        sortOrder: saved.sortOrder ?? defaults.sortOrder ?? 'desc',
        perPage: saved.perPage ?? defaults.perPage ?? 15,
        page: saved.page ?? 1,
        filters: { ...filterDefaults, ...(saved.filters ?? {}) },
        _debounceTimer: null,

        // Alpine watchers via `$watch` (declared in x-init in view kalau perlu)
        async load(page = null) {
            if (page !== null) this.page = page;
            this.loading = true;

            const params = new URLSearchParams({
                q: this.q,
                sort: this.sortBy || '',
                order: this.sortOrder,
                per_page: this.perPage,
                page: this.page,
            });
            // Merge filters
            for (const [k, v] of Object.entries(this.filters)) {
                if (v !== '' && v !== null && v !== undefined) {
                    params.set(k, v);
                }
            }

            try {
                const url = `${endpoint}?${params.toString()}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const json = await res.json();
                this.data = json.data || [];
                this.pagination = json.meta || json.pagination || {
                    current_page: json.current_page,
                    last_page: json.last_page,
                    total: json.total,
                    from: json.from,
                    to: json.to,
                };
                this._persist();
                this._cleanupUrl();
            } catch (err) {
                console.error('dataTable load failed:', err);
                this.data = [];
            } finally {
                this.loading = false;
            }
        },

        // Search dengan debounce
        onSearch() {
            clearTimeout(this._debounceTimer);
            this._debounceTimer = setTimeout(() => {
                this.page = 1; // reset ke halaman 1 saat search baru
                this.load();
            }, 300);
        },

        // Sort by column
        toggleSort(column) {
            if (this.sortBy === column) {
                if (this.sortOrder === 'asc') this.sortOrder = 'desc';
                else if (this.sortOrder === 'desc') { this.sortBy = null; this.sortOrder = 'desc'; }
                else this.sortOrder = 'asc';
            } else {
                this.sortBy = column;
                this.sortOrder = 'asc';
            }
            this.load();
        },

        sortIcon(column) {
            if (this.sortBy !== column) return '⇅';
            return this.sortOrder === 'asc' ? '▲' : '▼';
        },

        // Filter change
        onFilter() {
            this.page = 1;
            this.load();
        },

        // Reset all filters + search + sort
        reset() {
            this.q = '';
            this.sortBy = defaults.sortBy ?? null;
            this.sortOrder = defaults.sortOrder ?? 'desc';
            this.filters = { ...filterDefaults };
            this.page = 1;
            this.load();
        },

        // Pagination
        goToPage(p) {
            if (p < 1 || p > this.pagination.last_page) return;
            this.load(p);
            // Scroll ke atas table
            document.querySelector('main')?.scrollTo({ top: 0, behavior: 'smooth' });
        },

        // Pagination range helper: [1, ..., 4, 5, 6, ..., 20]
        pageRange() {
            const cur = this.pagination.current_page;
            const last = this.pagination.last_page;
            const range = [];
            const delta = 2;
            for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
                range.push(i);
            }
            if (range[0] > 1) range.unshift('...');
            if (range[0] !== 1) range.unshift(1);
            if (range[range.length - 1] < last) range.push('...');
            if (range[range.length - 1] !== last && last > 1) range.push(last);
            return range;
        },

        _persist() {
            if (!storageKey) return;
            try {
                localStorage.setItem(storageKey, JSON.stringify({
                    q: this.q,
                    sortBy: this.sortBy,
                    sortOrder: this.sortOrder,
                    perPage: this.perPage,
                    page: this.page,
                    filters: this.filters,
                }));
            } catch (e) {}
        },

        // Strip query string dari URL — user cuma lihat path bersih
        _cleanupUrl() {
            const path = window.location.pathname;
            if (window.location.search) {
                history.replaceState({}, '', path);
            }
        },
    };
}
