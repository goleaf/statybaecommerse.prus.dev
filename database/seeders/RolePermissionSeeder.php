<?php declare(strict_types=1);

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

        // Create permissions for enhanced settings
        $permissions = [
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Create user role with limited permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo([
            'view_enhanced_settings',
            'view_any_enhanced_settings',
        ]);
    }
}
