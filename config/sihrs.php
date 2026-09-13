<?php

/*
|--------------------------------------------------------------------------
| SIHRS - Konfigurasi Rumah Sakit
|--------------------------------------------------------------------------
| Identitas RS untuk kop dokumen, kuitansi, resume medis, laporan.
| Ubah di .env, bukan di sini.
*/

return [
    'rs_nama' => env('SIHRS_RS_NAMA', 'RS Sehat Sentosa'),
    'rs_alamat' => env('SIHRS_RS_ALAMAT', 'Jl. Merdeka No. 123, Medan'),
    'rs_telp' => env('SIHRS_RS_TELP', '(061) 1234567'),
    'rs_email' => env('SIHRS_RS_EMAIL', 'info@sihrs.local'),
    'rs_website' => env('SIHRS_RS_WEBSITE'),
    'rs_kode' => env('SIHRS_RS_KODE', '3275001'),
    'rs_izin' => env('SIHRS_RS_IZIN'), // No. izin operasional dari Dinkes
    'rs_kelas' => env('SIHRS_RS_KELAS', 'C'), // A, B, C, D

    /*
    | Kritis Alerting
    | ---------------------------------------
    | Kirim CC email kritis ke pihak lain (misal: manajer klinis / komdik) untuk audit.
    | Pisahkan dengan koma. Kosongkan untuk skip.
    */
    'kritis_cc' => array_filter(explode(',', env('SIHRS_KRITIS_CC', ''))),

    /*
    | Watermark untuk cetak ulang PDF
    */
    'pdf_watermark_salinan' => env('SIHRS_PDF_WATERMARK', true),

    /*
    | URL public untuk QR verification link. Default ke APP_URL.
    | Kalau ada domain verifikasi terpisah (mis. verify.rs.id), set di sini.
    */
    'verify_base_url' => env('SIHRS_VERIFY_BASE_URL', env('APP_URL')),
];
