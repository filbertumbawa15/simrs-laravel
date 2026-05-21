@php
    $user = auth()->user();
    $current = request()->route()?->getName() ?? '';
@endphp

<aside class="w-64 bg-gradient-to-b from-primary-800 to-primary-900 text-primary-50 flex flex-col">

    {{-- Brand --}}
    <div class="px-5 py-5 border-b border-primary-700">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 13h6m-3-3v6M5 7a2 2 0 012-2h10a2 2 0 012 2v12l-3-2-2 2-2-2-2 2-2-2-2 2V7z"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-lg leading-tight">SIHRS</div>
                <div class="text-xs text-primary-200">{{ config('app.rs.nama') }}</div>
            </div>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">

        @php
            $menu = [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'permission' => null],
                ['label' => 'Pasien', 'route' => 'pasien.index', 'icon' => 'users', 'permission' => 'pasien.view'],
                ['label' => 'Kunjungan', 'route' => 'kunjungan.index', 'icon' => 'clipboard', 'permission' => 'kunjungan.view'],
                ['label' => 'Antrian Poli (RJ)', 'route' => 'rj.antrian', 'icon' => 'queue', 'permission' => 'rj.view'],
                ['label' => 'Farmasi', 'route' => 'resep.index', 'icon' => 'pill', 'permission' => 'farmasi.view'],
                ['label' => 'Billing', 'route' => 'billing.index', 'icon' => 'cash', 'permission' => 'billing.view'],
            ];

            $iconPath = [
                'home' => 'M3 12l9-9 9 9M5 10v10h14V10',
                'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m13-14a4 4 0 11-8 0 4 4 0 018 0z',
                'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'queue' => 'M4 6h16M4 10h16M4 14h10M4 18h10',
                'pill' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'cash' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            ];
        @endphp

        @foreach ($menu as $item)
            @if (! $item['permission'] || $user->can($item['permission']))
                @php
                    $active = str_starts_with($current, str_replace('.index', '', $item['route']))
                              || $current === $item['route'];
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ $active ? 'bg-primary-700 text-white font-semibold' : 'text-primary-100 hover:bg-primary-700/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath[$item['icon']] }}"/>
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    {{-- User badge --}}
    <div class="px-3 py-3 border-t border-primary-700 text-xs text-primary-200">
        <div class="font-semibold text-white">{{ $user->name }}</div>
        <div class="truncate">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</div>
    </div>
</aside>