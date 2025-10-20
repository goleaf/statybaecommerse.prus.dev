<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesRolePermissions
{
    protected function allows(User $user, string $entity, string $action): bool
    {
        $ability = sprintf('%s.%s', $entity, $action);
        $allowedAbilities = $this->resolveAbilitiesForUser($user);

        foreach ($allowedAbilities as $allowed) {
            if ($allowed === '*' || $allowed === $ability || Str::is($allowed, $ability)) {
                return true;
            }
        }

        return false;
    }

    private function resolveAbilitiesForUser(User $user): Collection
    {
        $rolesConfig = config('permissions.roles', []);
        $aliases = config('permissions.aliases', []);

        return $user->getRoleNames()
            ->map(function (string $role) use ($rolesConfig, $aliases) {
                if (array_key_exists($role, $rolesConfig)) {
                    return $rolesConfig[$role];
                }

                if (array_key_exists($role, $aliases)) {
                    $alias = $aliases[$role];

                    return $rolesConfig[$alias] ?? [];
                }

                return [];
            })
            ->flatten()
            ->filter()
            ->values();
    }
}
