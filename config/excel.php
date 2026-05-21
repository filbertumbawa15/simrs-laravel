<?php

return [

    'exports' => [

        'chunk_size' => 1000,

        'pre_calculate_formulas' => false,

        'strict_null_comparison' => false,

        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
            'output_encoding' => '',
            'test_auto_detect' => true,
        ],

        'properties' => [
            'creator' => env('APP_NAME', 'SIHRS'),
            'lastModifiedBy' => env('APP_NAME', 'SIHRS'),
            'title' => 'SIHRS Report',
            'description' => 'Laporan dari Sistem Informasi Rumah Sakit',
            'subject' => '',
            'keywords' => 'sihrs,rumahsakit,laporan',
            'category' => 'Healthcare',
            'manager' => '',
            'company' => env('SIHRS_RS_NAMA', 'RS Sehat Sentosa'),
        ],

    ],

    'imports' => [

        'read_only' => true,

        'ignore_empty' => false,

        'heading_row' => [
            'formatter' => 'slug',
        ],

        'csv' => [
            'delimiter' => null,
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],

        'properties' => [
            'creator' => '',
            'lastModifiedBy' => '',
            'title' => '',
            'description' => '',
            'subject' => '',
            'keywords' => '',
            'category' => '',
            'manager' => '',
            'company' => '',
        ],

    ],

    'extension_detector' => [
        'xlsx' => Maatwebsite\Excel\Excel::XLSX,
        'xlsm' => Maatwebsite\Excel\Excel::XLSX,
        'xltx' => Maatwebsite\Excel\Excel::XLSX,
        'xltm' => Maatwebsite\Excel\Excel::XLSX,
        'xls' => Maatwebsite\Excel\Excel::XLS,
        'xlt' => Maatwebsite\Excel\Excel::XLS,
        'ods' => Maatwebsite\Excel\Excel::ODS,
        'ots' => Maatwebsite\Excel\Excel::ODS,
        'slk' => Maatwebsite\Excel\Excel::SLK,
        'xml' => Maatwebsite\Excel\Excel::XML,
        'gnumeric' => Maatwebsite\Excel\Excel::GNUMERIC,
        'htm' => Maatwebsite\Excel\Excel::HTML,
        'html' => Maatwebsite\Excel\Excel::HTML,
        'csv' => Maatwebsite\Excel\Excel::CSV,
        'tsv' => Maatwebsite\Excel\Excel::TSV,
        'pdf' => Maatwebsite\Excel\Excel::DOMPDF,
    ],

    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        'driver' => 'memory',
        'batch' => [
            'memory_limit' => 60000,
        ],
        'illuminate' => [
            'store' => null,
        ],
        'default_ttl' => 10800,
    ],

    'transactions' => [
        'handler' => 'db',
        'db' => [
            'connection' => null,
        ],
    ],

    'temporary_files' => [
        'local_path' => storage_path('framework/laravel-excel'),
        'local_permissions' => [],
        'remote_disk' => null,
        'remote_prefix' => null,
        'force_resync_remote' => null,
    ],

];
