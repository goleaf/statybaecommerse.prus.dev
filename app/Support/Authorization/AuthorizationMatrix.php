<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Enums\AuthorizationRole;
use Filament\FilamentManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use InvalidArgumentException;
use Throwable;

final class AuthorizationMatrix
{
    private const CONFIG_KEY = 'authorization';

    /** @var array<string, mixed>|null */
    private static ?array $cachedFileConfiguration = null;

    public static function ability(string $resource, string $ability): string
    {
        $permission = self::configValue(self::CONFIG_KEY . '.abilities.' . $resource . '.' . $ability);

        if (! is_string($permission) || $permission === '') {
            throw new InvalidArgumentException(sprintf('Unknown ability [%s.%s] requested.', $resource, $ability));
        }

        return $permission;
    }

    public static function check(string $resource, string $ability, ?Authenticatable $user = null): bool
    {
        $user ??= self::currentUser();

        if ($user === null) {
            return false;
        }

        return $user->can(self::ability($resource, $ability));
    }

    public static function currentUser(): ?Authenticatable
    {
        $auth = self::authFactory();

        if ($auth === null) {
            return null;
        }

        $guard = self::resolveGuardName();

        return $guard !== null
            ? $auth->guard($guard)->user()
            : $auth->guard()->user();
    }

    /**
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        $abilities = self::configValue(self::CONFIG_KEY . '.abilities', []);

        if (! is_array($abilities)) {
            return [];
        }

        $permissions = [];

        foreach ($abilities as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ($group as $permission) {
                if (! is_string($permission) || $permission === '') {
                    continue;
                }

                $permissions[$permission] = true;
            }
        }

        $list = array_keys($permissions);
        sort($list);

        return $list;
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsForRole(AuthorizationRole $role): array
    {
        $roles = self::configValue(self::CONFIG_KEY . '.roles', []);

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
            if (is_string($permission)) {
                $permission = trim($permission);

                if ($permission !== '') {
                    $normalized[$permission] = true;
                }
            }
        }

        return array_keys($normalized);
    }

    /**
     * @return array<int, array{role: AuthorizationRole, permissions: array<int, string>}>
     */
    public static function roles(): array
    {
        $definitions = [];

        foreach (AuthorizationRole::cases() as $role) {
            $configured = self::configValue(self::CONFIG_KEY . '.roles.' . $role->value);

            if ($configured === null) {
                continue;
            }

            $definitions[] = [
                'role' => $role,
                'permissions' => self::permissionsForRole($role),
            ];
        }

        return $definitions;
    }

    /**
     * @return array<int, string>
     */
    public static function guardNames(): array
    {
        $guards = self::configValue(self::CONFIG_KEY . '.guards', []);

        if (! is_array($guards)) {
            return [];
        }

        $normalized = [];

        foreach ($guards as $guard) {
            if (is_string($guard)) {
                $guard = trim($guard);

                if ($guard !== '') {
                    $normalized[$guard] = true;
                }
            }
        }

        return array_keys($normalized);
    }

    private static function resolveGuardName(): ?string
    {
        if (function_exists('app')) {
            try {
                if (app()->bound('filament')) {
                    $filament = app('filament');

                    if ($filament instanceof FilamentManager) {
                        $guard = $filament->getCurrentPanel()?->getAuthGuard();

                        if (is_string($guard) && $guard !== '') {
                            return $guard;
                        }
                    }
                }
            } catch (Throwable) {
                // Ignore resolution errors when the container is not bootstrapped.
            }
        }

        $configuredGuard = self::configValue('filament.auth.guard');
        if (is_string($configuredGuard) && $configuredGuard !== '') {
            return $configuredGuard;
        }

        $defaultGuard = self::configValue('auth.defaults.guard');
        if (is_string($defaultGuard) && $defaultGuard !== '') {
            return $defaultGuard;
        }

        return null;
    }

    private static function container(): ?Container
    {
        return Container::getInstance();
    }

    private static function authFactory(): ?AuthFactory
    {
        $container = self::container();

        if ($container === null || ! $container->bound('auth')) {
            return null;
        }

        try {
            $factory = $container->make('auth');
        } catch (Throwable) {
            return null;
        }

        return $factory instanceof AuthFactory ? $factory : null;
    }

    private static function configRepository(): ?ConfigRepository
    {
        $container = self::container();

        if ($container === null || ! $container->bound('config')) {
            return null;
        }

        try {
            $repository = $container->make('config');
        } catch (Throwable) {
            return null;
        }

        return $repository instanceof ConfigRepository ? $repository : null;
    }

    private static function configValue(string $key, mixed $default = null): mixed
    {
        $repository = self::configRepository();

        if ($repository !== null) {
            return $repository->get($key, $default);
        }

        if ($key === self::CONFIG_KEY) {
            return self::fileConfig();
        }

        if (str_starts_with($key, self::CONFIG_KEY . '.')) {
            $relativeKey = substr($key, strlen(self::CONFIG_KEY) + 1);

            return self::arrayGet(self::fileConfig(), $relativeKey, $default);
        }

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    private static function fileConfig(): array
    {
        if (self::$cachedFileConfiguration !== null) {
            return self::$cachedFileConfiguration;
        }

        $path = self::resolveConfigPath();

        if (is_file($path)) {
            $config = require $path;

            if (is_array($config)) {
                return self::$cachedFileConfiguration = $config;
            }
        }

        return self::$cachedFileConfiguration = [];
    }

    private static function resolveConfigPath(): string
    {
        if (function_exists('config_path')) {
            return config_path(self::CONFIG_KEY . '.php');
        }

        if (function_exists('base_path')) {
            return base_path('config/' . self::CONFIG_KEY . '.php');
        }

        return dirname(__DIR__, 3) . '/config/' . self::CONFIG_KEY . '.php';
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
}

