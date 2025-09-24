<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

        // Create or get the super admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        // Create basic permissions
        $permissions = [
            'access_admin_panel',
            'manage_all_users',
            'manage_all_orders',
            'manage_all_products',
            'manage_all_settings',
            'super_admin_access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to super admin role
        $superAdminRole->syncPermissions(Permission::all());

        // Create or update the super admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
