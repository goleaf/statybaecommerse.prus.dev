<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Address;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Country;
use App\Models\DiscountCondition;
use App\Models\Export;
use App\Models\Legal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ProductRequest;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VariantCombination;
use App\Policies\AddressPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CountryPolicy;
use App\Policies\DiscountConditionPolicy;
use App\Policies\ExportPolicy;
use App\Policies\LegalPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductRequestPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\ReferralPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use App\Policies\VariantCombinationPolicy;
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
        Address::class            => AddressPolicy::class,
        Brand::class              => BrandPolicy::class,
        Category::class           => CategoryPolicy::class,
        Country::class            => CountryPolicy::class,
        DiscountCondition::class  => DiscountConditionPolicy::class,
        Export::class             => ExportPolicy::class,
        Legal::class              => LegalPolicy::class,
        Notification::class       => NotificationPolicy::class,
        Order::class              => OrderPolicy::class,
        ProductRequest::class     => ProductRequestPolicy::class,
        Referral::class           => ReferralPolicy::class,
        ReferralCode::class       => ReferralCodePolicy::class,
        Role::class               => RolePolicy::class,
        SystemSetting::class      => SystemSettingPolicy::class,
        User::class               => UserPolicy::class,
        VariantCombination::class => VariantCombinationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        if ($this->app->runningUnitTests()) {
            // Grant blanket access in tests so feature assertions can focus on rendered UI states.
            // However, respect the skip_checks config setting to allow proper authorization testing.
            Gate::before(static function ($user, ?string $ability = null): ?bool {
                // If skip_checks is false, don't bypass authorization checks
                if (! (bool) config('authorization.testing.skip_checks', true)) {
                    return null;
                }

                return true;
            });
        }

        // Allow privileged admin users to bypass authorization checks
        Gate::before(function ($user, ?string $ability = null): ?true {
            if (! $user instanceof AdminUser) {
                return null;
            }

            return (bool) ($user->is_admin ?? false) ? true : null;
        });

        Gate::define('viewMailPreviews', static function (?Authenticatable $user = null): bool {
            if (app()->environment(['local', 'development', 'testing'])) {
                return true;
            }

            if (! $user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
                return false;
            }

            return property_exists($user, 'is_admin') && $user->is_admin !== null && (bool) $user->is_admin;
        });

        $permissions = (array) config('dashboard.permissions', []);

        foreach ($permissions as $permission) {
            Gate::define($permission, static function ($user): bool {
                return property_exists($user, 'is_admin') && (bool) $user->is_admin;
            });
        }
    }
}
