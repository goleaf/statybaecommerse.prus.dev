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
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Brand::class => BrandPolicy::class,
        Category::class => CategoryPolicy::class,
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, ?string $ability = null) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            if ($user->hasRole('admin') || $user->hasRole('administrator') || $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });
    }
}
