<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Enums\AuthorizationRole;
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
     *
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        $configuredAbilities = config(sprintf('%s.abilities', self::CONFIG_KEY), []);

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
        $roles = config(sprintf('%s.roles', self::CONFIG_KEY), []);

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
        $configuredRoles = config(sprintf('%s.roles', self::CONFIG_KEY), []);

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
        $guards = config(sprintf('%s.guards', self::CONFIG_KEY), []);

        if (! is_array($guards)) {
            return [];
        }

        return array_values(array_filter($guards, fn ($guard) => is_string($guard) && $guard !== ''));
    }
}
