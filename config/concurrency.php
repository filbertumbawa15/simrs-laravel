<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Concurrency Driver
    |--------------------------------------------------------------------------
    | Pilihan: 'process', 'fork', 'sync'
    | Untuk RS, 'sync' default cukup. Aktifkan 'fork' kalau perlu
    | parallel processing (misal batch klaim BPJS bulanan).
    */

    'default' => env('CONCURRENCY_DRIVER', 'process'),

];
