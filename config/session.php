<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    | Pilihan untuk RS:
    | - 'database' (default): aman, audit trail tersimpan
    | - 'redis': lebih cepat, tapi butuh setup Redis
    */
    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    | Default 2 jam. Untuk RS pertimbangkan:
    | - Dokter/Perawat di stasiun kerja: 8 jam (durasi shift)
    | - Kasir/Admisi: 4 jam
    | - Super admin: 1 jam
    | Bisa diatur via env per role di middleware kustom.
    */
    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    | Production WAJIB true — session bisa berisi data sensitif pasien.
    */
    'encrypt' => env('SESSION_ENCRYPT', true),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'sihrs'), '_').'_session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    | Production WAJIB true — cookie tidak boleh dikirim via HTTP biasa.
    */
    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    | 'lax' default. Set 'strict' kalau ingin lebih ketat (tidak ada flow
    | embed dari domain lain).
    */
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
