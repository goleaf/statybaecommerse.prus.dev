<?php declare(strict_types=1);

namespace App\Providers;

use App\Policies\RolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
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
