<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

final class AuthorizationMatrix
{
    private const CONFIG_KEY = 'authorization';

    private static ?array $configCache = null;

    /**
     * Resolve the permission string for a given resource ability.
     */
    public static function ability(string $resource, string $ability): string
    {
        $abilities = self::configSegment('abilities.'.$resource, []);

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
     */
    public static function allPermissions(): array
    {
        $abilities = collect(self::configSegment('abilities', []))
            ->filter(fn ($group) => is_array($group))
            ->flatMap(fn (array $group) => array_values(array_filter($group, fn ($permission) => is_string($permission) && $permission !== '')))
            ->unique()
            ->sort()
            ->values();

        return $abilities->all();
    }

    /**
     * Fetch the flattened permission list for a given role, expanding wildcards.
     */
    public static function permissionsForRole(string $role): array
    {
        $roles = self::roles();
        $permissions = $roles[$role] ?? [];

        if (! is_array($permissions)) {
            return [];
        }

        if (in_array('*', $permissions, true)) {
            return self::allPermissions();
        }

        return Collection::make($permissions)
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Expose all role definitions.
     */
    public static function roles(): array
    {
        return self::configSegment('roles', []);
    }

    /**
     * Expose guard names that should receive seeded permissions.
     */
    public static function guardNames(): array
    {
        return self::configSegment('guards', []);
    }

    private static function configSegment(string $segment, $default)
    {
        $config = self::resolvedConfig();
        $value = $config;

        foreach (explode('.', $segment) as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) {
                return $default;
            }

            $value = $value[$key];
        }

        return $value;
    }

    private static function resolvedConfig(): array
    {
        if (self::$configCache !== null) {
            return self::$configCache;
        }

        if (function_exists('config')) {
            try {
                $value = config(self::CONFIG_KEY);
                if (is_array($value)) {
                    return self::$configCache = $value;
                }
            } catch (Throwable) {
                // Fall through to file-based configuration.
            }
        }

        $path = dirname(__DIR__, 3).'/config/'.self::CONFIG_KEY.'.php';

        if (is_file($path)) {
            $value = require $path;

            if (is_array($value)) {
                return self::$configCache = $value;
            }
        }

        return self::$configCache = [];
    }
}
