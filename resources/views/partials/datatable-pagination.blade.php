{{--
    Reusable pagination footer untuk Alpine dataTable.
    Butuh: pagination, pageRange(), goToPage() dari x-data.
--}}
<div class="card-footer" x-show="pagination.last_page > 1">
    <nav class="flex items-center justify-between flex-wrap gap-3">
        <div class="text-xs text-gray-500">
            Halaman <strong x-text="pagination.current_page"></strong> dari <strong x-text="pagination.last_page"></strong>
            <span x-text="`· ${pagination.total} total`"></span>
        </div>
        <div class="flex items-center gap-1 flex-wrap">
            <button @click="goToPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                ‹
            </button>
            <template x-for="(p, idx) in pageRange()" :key="idx">
                <button @click="typeof p === 'number' && goToPage(p)"
                        :disabled="p === '...'"
                        :class="p === pagination.current_page ? 'bg-primary-600 text-white' : 'hover:bg-gray-100'"
                        class="px-3 py-1 text-sm border border-gray-200 rounded min-w-[36px]"
                        x-text="p"></button>
            </template>
            <button @click="goToPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                ›
            </button>
        </div>
    </nav>
</div>
