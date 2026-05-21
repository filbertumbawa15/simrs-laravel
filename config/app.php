<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'SIHRS'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */
    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */
    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    | Default ke Asia/Jakarta — bisa di-override per RS lewat .env
    | (Asia/Pontianak, Asia/Makassar, Asia/Jayapura)
    */
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locale' => env('APP_LOCALE', 'id'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    | WAJIB di-generate: php artisan key:generate
    | Production: simpan key di secret manager, jangan commit ke git
    */
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    */
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SIHRS — Profile RS
    |--------------------------------------------------------------------------
    | Identitas rumah sakit yang akan tampil di header, kop surat, kuitansi, dll.
    | Bisa juga ditarik dari tabel `rs_profile` kalau RS multi-cabang.
    */
    'rs' => [
        'nama' => env('SIHRS_RS_NAMA', 'RS Sehat Sentosa'),
        'alamat' => env('SIHRS_RS_ALAMAT', 'Jl. Merdeka No. 123, Medan'),
        'telp' => env('SIHRS_RS_TELP', '(061) 1234567'),
        'kode' => env('SIHRS_RS_KODE', '3275001'), // Kode RS dari Kemenkes
        'logo' => env('SIHRS_RS_LOGO', 'images/logo-rs.png'),
    ],
];
