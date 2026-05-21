<header class="h-16 bg-white border-b border-gray-200 px-6 flex items-center justify-between"
    x-data="{ now: '', open: false }"
    x-init="
            const fmt = () => new Date().toLocaleString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            now = fmt();
            setInterval(() => now = fmt(), 1000);
        ">

    {{-- Tanggal/jam realtime --}}
    <div class="text-sm text-gray-600">
        <span x-text="now"></span>
    </div>

    {{-- Quick actions + user --}}
    <div class="flex items-center gap-3">

        @can('pasien.create')
        <a href="{{ route('pasien.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Pasien Baru
        </a>
        @endcan

        {{-- User dropdown --}}
        <div class="relative" @click.outside="open = false">
            <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition">
                <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:inline">
                    {{ auth()->user()->name }}
                </span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg ring-1 ring-gray-200 py-1 z-50"
                style="display: none;">
                <div class="px-4 py-2 border-b border-gray-100">
                    <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>