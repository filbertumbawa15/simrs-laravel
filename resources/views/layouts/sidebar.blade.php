@php
    $user = auth()->user();
    $current = request()->route()?->getName() ?? '';

    // Helper: cek akses menu — SUPER_ADMIN selalu lolos,
    // selain itu cek by permission. Mencegah menu hilang gara-gara
    // permission belum ke-seed.
    $canAccess = function (?string $permission) use ($user) {
        if (! $user) return false;
        if ($user->hasRole('SUPER_ADMIN')) return true;
        if (! $permission) return true;
        return $user->can($permission);
    };
@endphp

<aside class="w-64 bg-gradient-to-b from-primary-800 to-primary-900 text-primary-50 flex flex-col">

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
                <div class="text-xs text-primary-200 truncate">{{ config('app.rs.nama') }}</div>
            </div>
        </a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-4 overflow-y-auto text-sm">

        @php
            $menuGroups = [
                'UTAMA' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'permission' => null],
                    ['label' => 'Pasien', 'route' => 'pasien.index', 'icon' => 'users', 'permission' => 'pasien.view'],
                    ['label' => 'Kunjungan', 'route' => 'kunjungan.index', 'icon' => 'clipboard', 'permission' => 'kunjungan.view'],
                ],
                'PELAYANAN' => [
                    ['label' => 'Antrian RJ', 'route' => 'rj.antrian', 'icon' => 'queue', 'permission' => 'rj.view'],
                    ['label' => 'IGD Board', 'route' => 'igd.board', 'icon' => 'cross', 'permission' => 'kunjungan.view'],
                    ['label' => 'Rawat Inap', 'route' => 'ri.index', 'icon' => 'bed', 'permission' => 'ri.view'],
                    ['label' => 'Bed Management', 'route' => 'kamar.board', 'icon' => 'grid', 'permission' => 'ri.view'],
                ],
                'PENUNJANG' => [
                    ['label' => 'Laboratorium', 'route' => 'lab.index', 'icon' => 'flask', 'permission' => 'lab.view'],
                    ['label' => 'Farmasi', 'route' => 'resep.index', 'icon' => 'pill', 'permission' => 'farmasi.view'],
                    ['label' => 'Radiologi', 'route' => 'rad.index', 'icon' => 'flask', 'permission' => 'rad.view'],
                ],
                'KEUANGAN' => [
                    ['label' => 'Billing', 'route' => 'billing.index', 'icon' => 'cash', 'permission' => 'billing.view'],
                ],
            ];

            $iconPath = [
                'home' => 'M3 12l9-9 9 9M5 10v10h14V10',
                'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m13-14a4 4 0 11-8 0 4 4 0 018 0z',
                'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'queue' => 'M4 6h16M4 10h16M4 14h10M4 18h10',
                'cross' => 'M12 5v14m-7-7h14',
                'bed' => 'M3 10h18M3 10v10m0-10l2-6h14l2 6M21 10v10M7 20v-4m10 4v-4',
                'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                'flask' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'pill' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'cash' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1L15 4m-3 1v1m0 6v1m0 1v2m0-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
        @endphp

        @foreach ($menuGroups as $groupLabel => $items)
            @php
                $visible = collect($items)->filter(fn($i) => $canAccess($i['permission']));
            @endphp
            @if ($visible->isNotEmpty())
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-primary-300 font-bold px-3 mb-1">
                        {{ $groupLabel }}
                    </div>
                    @foreach ($visible as $item)
                        @php
                            $routeBase = str_replace(['.index', '.antrian', '.board'], '', $item['route']);
                            $prefix = explode('.', $item['route'])[0];
                            $active = $current === $item['route']
                                || str_starts_with($current, $prefix.'.');
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ $active ? 'bg-primary-700 text-white font-semibold' : 'text-primary-100 hover:bg-primary-700/50' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath[$item['icon']] ?? '' }}"/>
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    <div class="px-3 py-3 border-t border-primary-700 text-xs text-primary-200">
        <div class="font-semibold text-white">{{ $user->name }}</div>
        <div class="truncate">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</div>
    </div>
</aside>
