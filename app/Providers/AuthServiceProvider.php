<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Allow administrators to bypass all authorization checks
        Gate::before(function ($user, string $ability = null) {
            if (method_exists($user, 'hasRole') && $user->hasRole('administrator')) {
                return true;
            }

            return null;
        });
    }
}
