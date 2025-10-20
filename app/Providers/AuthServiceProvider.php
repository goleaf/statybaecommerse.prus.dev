<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Export;
use App\Policies\ExportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Export::class => ExportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Allow administrators to bypass all authorization checks
        Gate::before(function ($user, ?string $ability = null) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('administrator') || $user->hasRole('super_admin'))) {
                return true;
            }

            return null;
        });
    }
}
