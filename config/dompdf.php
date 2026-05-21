<?php

return [

    /*
     * Including the public path, by default the rootDir setting
     * is set to the application's root.
     */
    'show_warnings' => false,

    /*
     * Public path:
     */
    'public_path' => null,

    /*
     * Convert PHP errors into exceptions when not in debug mode.
     */
    'convert_entities' => true,

    'options' => [
        /*
         * The location of the DOMPDF font directory
         */
        'font_dir' => storage_path('fonts'),

        /*
         * The location of the DOMPDF font cache directory
         */
        'font_cache' => storage_path('fonts'),

        /*
         * The location of a temporary directory.
         */
        'temp_dir' => sys_get_temp_dir(),

        /*
         * Default paper size — A4 standar dokumen RS Indonesia
         */
        'default_paper_size' => 'a4',

        /*
         * Default paper orientation
         */
        'default_paper_orientation' => 'portrait',

        /*
         * The default font family
         */
        'default_font' => 'sans-serif',

        /*
         * Image DPI setting
         */
        'dpi' => 96,

        /*
         * Enable embedded PHP — JANGAN aktifkan di production
         * (security risk: PDF bisa execute PHP)
         */
        'enable_php' => false,

        /*
         * Enable inline JavaScript
         */
        'enable_javascript' => true,

        /*
         * Enable remote file access
         */
        'enable_remote' => true,

        /*
         * List of allowed remote hosts.
         * Empty = allow all (NOT recommended production).
         * Production: isi dengan domain logo RS, asset CDN saja.
         */
        'allowed_remote_hosts' => null,

        'font_height_ratio' => 1.1,

        'enable_html5_parser' => true,
    ],

];
