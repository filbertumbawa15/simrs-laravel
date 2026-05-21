<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Strict mode untuk catch bug development
        Model::shouldBeStrict(! $this->app->isProduction());

        // Pagination styling untuk Tailwind
        Paginator::useTailwind();

        // Set locale ke Indonesian (untuk format tanggal Carbon)
        \Carbon\Carbon::setLocale('id');
    }
}
