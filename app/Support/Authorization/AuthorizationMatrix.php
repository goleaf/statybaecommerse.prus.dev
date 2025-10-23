<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Enums\AuthorizationRole;
use Filament\FilamentManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use InvalidArgumentException;
use Throwable;

final class AuthorizationMatrix
{
    private const CONFIG_KEY = 'authorization';

    private static ?array $cachedConfiguration = null;

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

        if (! $auth) {
            return null;
        }

        $defaultGuard = self::config('auth.defaults.guard');

        if (is_string($guard) && $guard !== '') {
            return $auth->guard($guard)->user();
        }

        $defaultGuard = self::configValue('auth.defaults.guard');

        if (is_string($defaultGuard) && $defaultGuard !== '') {
            return $auth->guard($defaultGuard)->user();
        }

        return $auth->guard()->user();
    }

    /**
     * Resolve all permissions defined in the matrix.
     *
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        $configuredAbilities = self::configValue(sprintf('%s.abilities', self::CONFIG_KEY), []);

        if (! is_array($abilities)) {
            return [];
        }

        $permissions = [];

        foreach ($abilities as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ($group as $permission) {
                if (is_string($permission) && $permission !== '') {
                    $permissions[$permission] = true;
                }
            }
        }

        $permissionList = array_keys($permissions);
        sort($permissionList);

        return $permissionList;
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

        $normalized = [];

        foreach ($permissions as $permission) {
            if (is_string($permission) && $permission !== '') {
                $normalized[$permission] = true;
            }
        }

        return array_values(array_keys($normalized));
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

        return is_array($roles) ? $roles : [];
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

        return is_array($guards) ? $guards : [];
    }

    /**
     * Resolve the active container instance if available.
     */
    private static function container(): Container
    {
        return Container::getInstance();
    }

    /**
     * Resolve the configuration repository when the container is bound.
     */
    private static function configRepository(): ?ConfigRepository
    {
        $container = self::container();

        if (! $container->bound('config')) {
            return null;
        }

        try {
            $repository = $container->make('config');
        } catch (Throwable) {
            return null;
        }

        if (! $repository instanceof ConfigRepository) {
            return null;
        }

        return $repository;
    }

    /**
     * Resolve configuration values, falling back to the on-disk configuration when necessary.
     */
    private static function configValue(string $key, mixed $default = null): mixed
    {
        $repository = self::configRepository();

        if ($repository) {
            return $repository->get($key, $default);
        }

        if (! str_starts_with($key, self::CONFIG_KEY.'.')) {
            return $default;
        }

        $relativeKey = substr($key, strlen(self::CONFIG_KEY) + 1);
        $segments = explode('.', $relativeKey);
        $config = self::authorizationConfig();

        foreach ($segments as $segment) {
            if (! is_array($config) || ! array_key_exists($segment, $config)) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Provide access to the cached authorization configuration file.
     */
    private static function authorizationConfig(): array
    {
        static $authorizationConfig = null;

        if ($authorizationConfig !== null) {
            return $authorizationConfig;
        }

        $path = dirname(__DIR__, 3).'/config/'.self::CONFIG_KEY.'.php';

        if (is_file($path)) {
            $config = require $path;

            if (is_array($config)) {
                /** @var array<string, mixed> $config */
                return $authorizationConfig = $config;
            }
        }

        return $authorizationConfig = [];
    }

    /**
     * Resolve the authentication factory if one is bound in the container.
     */
    private static function authFactory(): ?AuthFactory
    {
        $container = self::container();

        if (! $container->bound('auth')) {
            return null;
        }

        try {
            $factory = $container->make('auth');
        } catch (Throwable) {
            return null;
        }

        if (! $factory instanceof AuthFactory) {
            return null;
        }

        return $factory;
    }

    private static function config(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            try {
                return config($key, $default);
            } catch (\Throwable $exception) {
                // Fallback to manual configuration loading when the container isn't bootstrapped.
            }
        }

        if (self::$cachedConfiguration === null) {
            $basePath = \function_exists('base_path')
                ? base_path()
                : dirname(__DIR__, 4);

            $configPath = $basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'authorization.php';
            self::$cachedConfiguration = file_exists($configPath) ? require $configPath : [];
        }

        if (! str_starts_with($key, self::CONFIG_KEY)) {
            return $default;
        }

        $relativeKey = substr($key, strlen(self::CONFIG_KEY) + 1) ?: '';

        return self::arrayGet(self::$cachedConfiguration, $relativeKey, $default);
    }

    private static function arrayGet(array $source, string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $source;
        }

        $segments = explode('.', $key);
        $value = $source;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Safely fetch authorization configuration regardless of the Laravel helper context.
     */
}
