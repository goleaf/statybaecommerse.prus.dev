<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';
        $rolesConfig = config('permissions.roles', []);
        $aliases = config('permissions.aliases', []);
        $entities = config('permissions.entities', []);

        $permissionNames = Collection::make($rolesConfig)
            ->merge(Collection::make($aliases)->mapWithKeys(fn (string $target) => [$target => $rolesConfig[$target] ?? []]))
            ->flatMap(fn (array $abilities) => $this->expandAbilities($abilities, $entities))
            ->unique()
            ->sort()
            ->values();

        $permissions = $permissionNames->map(fn (string $name) => Permission::findOrCreate($name, $guard));

        $roleNames = Collection::make(array_keys($rolesConfig))
            ->merge(array_keys($aliases))
            ->unique()
            ->values();

        $roleNames->each(function (string $roleName) use ($rolesConfig, $aliases, $entities, $permissions, $guard): void {
            $resolvedRole = $aliases[$roleName] ?? $roleName;
            $role = Role::findOrCreate($roleName, $guard);
            $abilityNames = $this->expandAbilities($rolesConfig[$resolvedRole] ?? [], $entities);
            $rolePermissions = $permissions->filter(fn (Permission $permission) => $abilityNames->contains($permission->name));

            $role->syncPermissions($rolePermissions);
        });
    }

    /**
     * @param array<int, string> $abilities
     */
    private function expandAbilities(array $abilities, array $entities): Collection
    {
        return Collection::make($abilities)
            ->flatMap(function (string $ability) use ($entities) {
                if ($ability === '*') {
                    return Collection::make($entities)
                        ->flatMap(fn (array $actions, string $entity) => Collection::make($actions)->map(fn (string $action) => sprintf('%s.%s', $entity, $action)));
                }

                if (Str::endsWith($ability, '.*')) {
                    $entity = Str::before($ability, '.');
                    $actions = $entities[$entity] ?? [];

                    return Collection::make($actions)->map(fn (string $action) => sprintf('%s.%s', $entity, $action));
                }

                return [$ability];
            })
            ->unique()
            ->values();
    }
}
