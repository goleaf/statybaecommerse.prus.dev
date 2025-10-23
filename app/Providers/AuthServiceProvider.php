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
     * The policy mappings for the application.
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Brand::class => BrandPolicy::class,
        Order::class => OrderPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Allow privileged admin roles to bypass granular authorization checks
        Gate::before(function ($user, ?string $ability = null) {
            if (! $user instanceof AdminUser) {
                return null;
            }

            if (property_exists($user, 'is_admin') && (bool) $user->is_admin) {
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
