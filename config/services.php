<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    | Kredensial integrasi disimpan di .env, dipanggil dari sini.
    | JANGAN commit credentials ke git.
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | BPJS Bridging — V-Claim, Antrol, Apotek Online
    |--------------------------------------------------------------------------
    | Dapatkan kredensial dari BPJS Kantor Cabang setempat.
    | Test env biasanya pakai consid berbeda dari production.
    */
    'bpjs' => [
        'vclaim' => [
            'base_url' => env('BPJS_VCLAIM_URL'),
            'cons_id' => env('BPJS_VCLAIM_CONSID'),
            'secret_key' => env('BPJS_VCLAIM_SECRET_KEY'),
            'user_key' => env('BPJS_VCLAIM_USER_KEY'),
            'timeout' => env('BPJS_TIMEOUT', 30),
        ],
        'antrol' => [
            'base_url' => env('BPJS_ANTROL_URL'),
            'cons_id' => env('BPJS_ANTROL_CONSID'),
            'secret_key' => env('BPJS_ANTROL_SECRET_KEY'),
            'user_key' => env('BPJS_ANTROL_USER_KEY'),
        ],
        'apotek' => [
            'base_url' => env('BPJS_APOTEK_URL'),
            'cons_id' => env('BPJS_APOTEK_CONSID'),
            'secret_key' => env('BPJS_APOTEK_SECRET_KEY'),
            'user_key' => env('BPJS_APOTEK_USER_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SATUSEHAT — Platform Interoperabilitas Kemenkes (WAJIB sejak 2023)
    |--------------------------------------------------------------------------
    | Daftar di: https://satusehat.kemkes.go.id
    | Standar: FHIR R4
    */
    'satusehat' => [
        'base_url' => env('SATUSEHAT_BASE_URL', 'https://api-satusehat.kemkes.go.id'),
        'auth_url' => env('SATUSEHAT_AUTH_URL', 'https://api-satusehat.kemkes.go.id/oauth2/v1'),
        'client_id' => env('SATUSEHAT_CLIENT_ID'),
        'client_secret' => env('SATUSEHAT_CLIENT_SECRET'),
        'organization_id' => env('SATUSEHAT_ORGANIZATION_ID'),
        'timeout' => env('SATUSEHAT_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | SIRANAP — Pelaporan tempat tidur ke Kemenkes (real-time)
    |--------------------------------------------------------------------------
    */
    'siranap' => [
        'base_url' => env('SIRANAP_URL'),
        'api_key' => env('SIRANAP_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway (untuk QRIS/EDC online)
    |--------------------------------------------------------------------------
    | Pilihan: Midtrans (umum di Indonesia), Xendit, atau langsung ke
    | bank acquirer (BCA, Mandiri).
    */
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds' => env('MIDTRANS_IS_3DS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway (untuk notifikasi hasil lab, pengingat janji)
    |--------------------------------------------------------------------------
    */
    'wablas' => [
        'token' => env('WABLAS_TOKEN'),
        'base_url' => env('WABLAS_BASE_URL', 'https://wablas.com/api'),
    ],

];
