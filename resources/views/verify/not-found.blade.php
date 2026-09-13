<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 text-center">
    <div class="text-6xl">🔍</div>
    <div class="text-red-700 font-bold text-lg mt-4">Dokumen Tidak Ditemukan</div>
    <div class="text-gray-500 text-sm mt-2">
        QR ini tidak merujuk ke dokumen yang tercatat.
        Kemungkinan dokumen palsu atau URL rusak.
    </div>
    <div class="mt-4 text-xs text-gray-400">
        {{ config('sihrs.rs_nama') }} · Telp: {{ config('sihrs.rs_telp') }}
    </div>
</div>
</body>
</html>
