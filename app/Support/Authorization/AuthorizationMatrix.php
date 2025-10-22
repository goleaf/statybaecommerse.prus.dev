<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Enums\AuthorizationRole;
use Filament\FilamentManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

final class AuthorizationMatrix
{
    private const CONFIG_KEY = 'authorization';

    /**
     * Resolve the permission string for a given resource ability.
     */
    public static function ability(string $resource, string $ability): string
    {
        $abilities = self::configValue(sprintf('%s.abilities.%s', self::CONFIG_KEY, $resource));

        if (! is_array($abilities) || ! array_key_exists($ability, $abilities)) {
            throw new InvalidArgumentException(sprintf('Unknown ability [%s.%s] requested.', $resource, $ability));
        }

        $permission = $abilities[$ability];

        if (! is_string($permission) || $permission === '') {
            throw new InvalidArgumentException(sprintf('Invalid permission mapping for ability [%s.%s].', $resource, $ability));
        }

        return $permission;
    }

    /**
     * Determine whether the supplied (or current) user can perform the ability.
     */
    public static function check(string $resource, string $ability, ?Authenticatable $user = null): bool
    {
        $user ??= self::currentUser();

        if (! $user) {
            return false;
        }

        return $user->can(self::ability($resource, $ability));
    }

    /**
     * Retrieve the authenticated user for the active Filament guard.
     */
    public static function currentUser(): ?Authenticatable
    {
        // Always prefer the active Filament panel guard so admin-only
        // requests authenticate against the dedicated admin guard when
        // Filament is booted without an explicit `filament.auth.guard`
        // configuration entry. We guard the container lookup to avoid
        // triggering facade resolution failures when Filament is not
        // registered (e.g. storefront-only CLI tasks).
        if (app()->bound('filament')) {
            /** @var FilamentManager $filament */
            $filament = app('filament');

            $panelGuard = $filament->getCurrentPanel()?->getAuthGuard();

            if (is_string($panelGuard) && $panelGuard !== '') {
                return auth()->guard($panelGuard)->user();
            }
        }

        $guard = config('filament.auth.guard');

        if (is_string($guard) && $guard !== '') {
            return auth()->guard($guard)->user();
        }

        $defaultGuard = config('auth.defaults.guard');

        return is_string($defaultGuard) && $defaultGuard !== ''
            ? auth()->guard($defaultGuard)->user()
            : auth()->user();
    }

    /**
     * Resolve all permissions defined in the matrix.
     *
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        $configuredAbilities = self::configValue(sprintf('%s.abilities', self::CONFIG_KEY), []);

        if (! is_array($configuredAbilities)) {
            return [];
        }

        $abilities = collect($configuredAbilities)
            ->filter(fn ($group) => is_array($group))
            ->flatMap(fn (array $group) => array_values(array_filter($group, fn ($permission) => is_string($permission) && $permission !== '')))
            ->unique()
            ->sort()
            ->values();

        return $abilities->all();
    }

    /**
     * Fetch the flattened permission list for a given role, expanding wildcards.
     *
     * @return array<int, string>
     */
    public static function permissionsForRole(AuthorizationRole $role): array
    {
        $roles = self::configValue(sprintf('%s.roles', self::CONFIG_KEY), []);

        if (! is_array($roles)) {
            return [];
        }

        $permissions = $roles[$role->value] ?? [];

        if (! is_array($permissions)) {
            return [];
        }

        if (in_array('*', $permissions, true)) {
            return self::allPermissions();
        }

        return array_values(Collection::make($permissions)
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->unique()
            ->values()
            ->all());
    }

    /**
     * Expose all role definitions paired with their configured permissions.
     *
     * @return array<int, array{role: AuthorizationRole, permissions: array<int, string>}>
     */
    public static function roles(): array
    {
        $roleDefinitions = [];
        $configuredRoles = self::configValue(sprintf('%s.roles', self::CONFIG_KEY), []);

        if (! is_array($configuredRoles)) {
            return [];
        }

        foreach ($configuredRoles as $role => $permissions) {
            if (! is_string($role)) {
                continue;
            }

            $enum = AuthorizationRole::tryFrom($role);

            if ($enum === null) {
                continue;
            }

            $roleDefinitions[] = [
                'role' => $enum,
                'permissions' => Collection::make(is_array($permissions) ? $permissions : [])
                    ->filter(fn ($permission) => is_string($permission) && $permission !== '')
                    ->values()
                    ->all(),
            ];
        }

        return $roleDefinitions;
    }

    /**
     * Expose guard names that should receive seeded permissions.
     *
     * @return array<int, string>
     */
    public static function guardNames(): array
    {
        $guards = self::configValue(sprintf('%s.guards', self::CONFIG_KEY), []);

        if ($guards === []) {
            return [];
        }

        if (! is_array($guards)) {
            return [];
        }

        return array_values(array_filter($guards, fn ($guard) => is_string($guard) && $guard !== ''));
    }

    /**
     * Safely fetch authorization configuration regardless of the Laravel helper context.
     */
    private static function configValue(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            try {
                if (! function_exists('app') || ! app()->bound('config')) {
                    throw new InvalidArgumentException('Config repository not bound.');
                }

                return config($key, $default);
            } catch (Throwable) {
                // Swallow the exception and fall back to the raw configuration file below.
            }
        }

        // Load and cache the raw authorization configuration when helper functions
        // are unavailable (for example in isolated PHPUnit unit tests).
        static $authorizationConfig;

        if ($authorizationConfig === null) {
            $basePath = dirname(__DIR__, 3);
            $configPath = $basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'authorization.php';

            $authorizationConfig = file_exists($configPath) ? (require $configPath) : [];
        }

        if (! str_starts_with($key, self::CONFIG_KEY)) {
            return $default;
        }

        $relativeKey = substr($key, strlen(self::CONFIG_KEY));
        $relativeKey = ltrim($relativeKey, '.');

        if ($relativeKey === '') {
            return $authorizationConfig;
        }

        $segments = explode('.', $relativeKey);
        $value = $authorizationConfig;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
