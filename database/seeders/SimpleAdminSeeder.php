<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * SimpleAdminSeeder
 *
 * Creates the super admin user without loading Filament resources
 */
final class SimpleAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Creating Super Admin User...');

        // Create basic permissions (Spatie models don't have factories by default)
        $permissionNames = [
            'access_admin_panel',
            'manage_all_users',
            'manage_all_orders',
            'manage_all_products',
            'manage_all_settings',
            'super_admin_access',
        ];

        $permissions = collect($permissionNames)->map(function ($permissionName) {
            return Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        });

        // Create super admin role (Spatie models don't have factories by default)
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        // Assign permissions to super admin role
        $superAdminRole->syncPermissions($permissions);

        // Create super admin user (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                'password' => 'password',  // Will be hashed by User model mutator
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        // Assign super admin role to user
        $admin->assignRole($superAdminRole);

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: admin@example.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('🎭 Role: super-admin');
    }
}
