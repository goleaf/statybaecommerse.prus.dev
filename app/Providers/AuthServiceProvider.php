<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Address;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Export;
use App\Models\Legal;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AddressPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ExportPolicy;
use App\Policies\LegalPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductRequestPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\ReferralPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
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
        Address::class => AddressPolicy::class,
        Brand::class => BrandPolicy::class,
        Category::class => CategoryPolicy::class,
        Customer::class => CustomerPolicy::class,
        Export::class => ExportPolicy::class,
        Legal::class => LegalPolicy::class,
        Notification::class => NotificationPolicy::class,
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        ProductRequest::class => ProductRequestPolicy::class,
        Referral::class => ReferralPolicy::class,
        ReferralCode::class => ReferralCodePolicy::class,
        Role::class => RolePolicy::class,
        SystemSetting::class => SystemSettingPolicy::class,
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

            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            return $user->hasAnyRole(['administrator', 'super_admin'])
                ? true
                : null;
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
