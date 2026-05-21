<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-700 to-primary-900 px-4">

    <div class="w-full max-w-md">

        {{-- Logo & brand --}}
        <div class="text-center mb-6 text-white">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-white/10 backdrop-blur items-center justify-center mb-3">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 13h6m-3-3v6M5 7a2 2 0 012-2h10a2 2 0 012 2v12l-3-2-2 2-2-2-2 2-2-2-2 2V7z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">SIHRS</h1>
            <p class="text-sm text-primary-200">{{ config('app.rs.nama') }}</p>
        </div>

        {{-- Login card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Masuk ke akun Anda</h2>

            @if ($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="label">Username</label>
                    <input id="username" name="username" type="text" required autofocus
                        value="{{ old('username') }}"
                        class="input @error('username') input-error @enderror"
                        placeholder="contoh: dr.andika">
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password" required
                        class="input @error('password') input-error @enderror">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember"
                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Ingat saya di komputer ini
                </label>

                <button type="submit" class="btn-primary w-full btn-lg">
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-500">
                Lupa password? Hubungi administrator IT
            </div>
        </div>

        <div class="text-center mt-4 text-xs text-primary-200">
            © {{ date('Y') }} {{ config('app.rs.nama') }} • SIHRS v1.0
        </div>
    </div>

</body>

</html>