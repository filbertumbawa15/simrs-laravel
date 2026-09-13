<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        // Force HTTPS di production — session cookies aman & no mixed content
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Pagination styling untuk Tailwind
        Paginator::useTailwind();

        // Set locale ke Indonesian (untuk format tanggal Carbon)
        \Carbon\Carbon::setLocale('id');

        $this->configureRateLimiters();
    }

    /**
     * Rate limiters untuk endpoint yang rentan brute-force / abuse.
     */
    protected function configureRateLimiters(): void
    {
        // Login — throttle by IP + username; 5 percobaan/menit.
        // Anti brute force credential stuffing.
        RateLimiter::for('login', function (Request $request) {
            $username = (string) $request->input('username');

            return Limit::perMinute(5)
                ->by($username . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return back()->withInput($request->only('username'))->withErrors([
                        'username' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                    ], 'default')->setStatusCode(429);
                });
        });

        // API bulk (bila nanti dipakai): 60 req/menit per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Write endpoint (pasien / kunjungan create) — 30 req/menit per user.
        // Anti spam / typo-bot / kesalahan operasional (mis. double-click submit).
        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(30)->by(optional($request->user())->id ?: $request->ip());
        });

        // Search endpoint (ICD, obat autocomplete) — 120 req/menit.
        // Autocomplete bisa fire banyak req saat user ngetik cepat.
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
