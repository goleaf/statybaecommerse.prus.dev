<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Authorization\AuthorizationMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class AdminAuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);

        foreach (AuthorizationMatrix::guardNames() as $guard) {
            $registrar->forgetCachedPermissions();

            $allPermissions = AuthorizationMatrix::allPermissions();

            foreach ($allPermissions as $permission) {
                Permission::firstOrCreate([
                    'name'       => $permission,
                    'guard_name' => $guard,
                ]);
            }

            foreach (AuthorizationMatrix::roles() as ['role' => $role]) {
                $roleModel = Role::firstOrCreate([
                    'name'       => $role->value,
                    'guard_name' => $guard,
                ]);

                $permissions = AuthorizationMatrix::permissionsForRole($role);

                if ($permissions === []) {
                    $roleModel->syncPermissions([]);

                    continue;
                }

                $roleModel->syncPermissions(
                    Permission::query()
                        ->where('guard_name', $guard)
                        ->whereIn('name', $permissions)
                        ->pluck('name')
                );
            }
        }

        $registrar->forgetCachedPermissions();
    }
}
