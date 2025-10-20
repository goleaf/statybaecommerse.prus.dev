<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
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

        Gate::define('viewMailPreviews', static function (?Authenticatable $user = null): bool {
            if (app()->environment(['local', 'development', 'testing'])) {
                return true;
            }

            if ($user === null) {
                return false;
            }

            return isset($user->is_admin) ? (bool) $user->is_admin : false;
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
