<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen - {{ config('sihrs.rs_nama') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
    <div class="text-center mb-4">
        <div class="text-sm text-gray-500">{{ config('sihrs.rs_nama') }}</div>
        <div class="text-xs text-gray-400">Verifikasi Keaslian Dokumen</div>
    </div>

    @if($valid)
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-center">
            <div class="text-emerald-700 font-bold text-lg">✓ DOKUMEN SAH</div>
            <div class="text-emerald-600 text-sm mt-1">Terdaftar dalam sistem SIHRS</div>
        </div>
    @else
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <div class="text-red-700 font-bold text-lg">✗ DOKUMEN TIDAK BERLAKU</div>
            <div class="text-red-600 text-sm mt-1">Status: {{ $status }}</div>
        </div>
    @endif

    <div class="mt-6 space-y-2 text-sm">
        <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500">Jenis</span>
            <span class="font-medium">{{ $title }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500">Nomor</span>
            <span class="font-mono">{{ $no }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500">Tanggal</span>
            <span>{{ $tanggal }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
            <span class="text-gray-500">Referensi</span>
            <span>{{ $ref }}</span>
        </div>
    </div>

    <div class="mt-6 text-center text-xs text-gray-400">
        Halaman ini adalah verifikasi otomatis. Untuk pertanyaan lebih lanjut hubungi {{ config('sihrs.rs_telp') }}.
    </div>
</div>
</body>
</html>
