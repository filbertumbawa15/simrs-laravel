<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Top bar --}}
            @include('layouts.topbar')

            {{-- Page content --}}
            <main class="flex-1 px-6 py-6">

                {{-- Page header --}}
                @hasSection('page-header')
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title')</h1>
                        @hasSection('page-subtitle')
                        <p class="text-sm text-gray-500 mt-0.5">@yield('page-subtitle')</p>
                        @endif
                    </div>
                    <div>@yield('page-actions')</div>
                </div>
                @endif

                {{-- Flash messages --}}
                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                {{-- Validation errors global --}}
                @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Periksa kembali input:</strong>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Konten halaman --}}
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>