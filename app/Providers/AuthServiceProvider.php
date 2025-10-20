<?php

declare(strict_types=1);

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
        Gate::before(function ($user, ?string $ability = null) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('administrator') || $user->hasRole('super_admin'))) {
                return true;
            }

            if (property_exists($user, 'is_admin') && (bool) $user->is_admin) {
                return true;
            }

            return null;
        });

        $permissions = (array) config('dashboard.permissions');

        foreach ($permissions as $permission) {
            Gate::define($permission, static function ($user) use ($permission): bool {
                if (method_exists($user, 'getAllPermissions') && $user->getAllPermissions()->contains('name', $permission)) {
                    return true;
                }

                return property_exists($user, 'is_admin') && (bool) $user->is_admin;
            });
        }
    }
}
