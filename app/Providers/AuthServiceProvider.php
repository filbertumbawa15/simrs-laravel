<?php

namespace App\Providers;

use App\Models\Pasien;
use App\Policies\PasienPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Pasien::class => PasienPolicy::class,
    ];

    public function boot(): void
    {
        // Super admin bypass — semua gate akan return true
        Gate::before(function ($user, $ability) {
            return $user->hasRole('SUPER_ADMIN') ? true : null;
        });
    }
}
