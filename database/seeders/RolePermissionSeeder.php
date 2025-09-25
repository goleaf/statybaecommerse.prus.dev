<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

        $permissions = collect($permissionNames)->map(function ($permissionName) {
            return Permission::firstOrCreate(['name' => $permissionName]);
        });

        // Create roles using factory relationships
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->syncPermissions($permissions);

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions($permissions->whereIn('name', [
            'view_enhanced_settings',
            'view_any_enhanced_settings',
        ]));
    }
}
