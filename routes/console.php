<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
| Aktifkan dengan cron di server produksi:
|   * * * * * cd /var/www/sihrs && php artisan schedule:run >> /dev/null 2>&1
*/

// Backup harian jam 02:00 dini hari — jam pasien paling sedikit
Schedule::command('sihrs:backup')
    ->dailyAt('02:00')
    ->onOneServer()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup.log'));