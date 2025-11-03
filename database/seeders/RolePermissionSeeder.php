<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'admin';

        // Create permissions for enhanced settings using factory
        $permissionNames = [
            'view_enhanced_settings',
            'create_enhanced_settings',
            'edit_enhanced_settings',
            'delete_enhanced_settings',
            'view_any_enhanced_settings',
            'force_delete_enhanced_settings',
            'restore_enhanced_settings',
            'replicate_enhanced_settings',
            'reorder_enhanced_settings',
        ];

        $permissions = collect($permissionNames)->map(function (string $permissionName) use ($guardName) {
            $permission = Permission::query()->where('name', $permissionName)->first();

            if ($permission === null) {
                return Permission::create([
                    'name'       => $permissionName,
                    'guard_name' => $guardName,
                ]);
            }

            if ($permission->guard_name !== $guardName) {
                $permission->guard_name = $guardName;
                $permission->save();
            }

            return $permission;
        });

        // Create roles using factory relationships
        $adminRole = $this->ensureRoleWithGuard('admin', $guardName);
        $adminRole->syncPermissions($permissions);

        $superAdminRole = $this->ensureRoleWithGuard('super_admin', $guardName);
        $superAdminRole->syncPermissions($permissions);

        $userRole = $this->ensureRoleWithGuard('user', $guardName);
        $userRole->syncPermissions($permissions->whereIn('name', [
            'view_enhanced_settings',
            'view_any_enhanced_settings',
        ]));
    }

    private function ensureRoleWithGuard(string $name, string $guardName): Role
    {
        $role = Role::query()->where('name', $name)->first();

        if ($role === null) {
            return Role::create([
                'name'       => $name,
                'guard_name' => $guardName,
            ]);
        }

        if ($role->guard_name !== $guardName) {
            $role->guard_name = $guardName;
            $role->save();
        }

        return $role;
    }
}
