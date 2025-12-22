<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Creating Super Admin User...');

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web']
        );

        // Create permissions manually instead of using factory
        $permissionsList = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'settings.view', 'settings.edit',
            'reports.view', 'reports.export',
        ];

        $permissions = collect($permissionsList)->map(function ($permission) {
            return Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        });

        $superAdminRole->syncPermissions($permissions);

        // Use updateOrCreate instead of factory()->create()
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'       => 'Super Administrator',
                'first_name' => 'Super',
                'last_name'  => 'Administrator',
                // Use a strong password so SecurePasswordHandling validates before hashing.
                'password'          => 'Admin123!',
                'is_admin'          => true,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([$superAdminRole]);

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: admin@example.com');
        // Keep the printed credentials aligned with the seeded password.
        $this->command->info('🔑 Password: Admin123!');
        $this->command->info('🎭 Role: super-admin with ' . $permissions->count() . ' permissions');
    }
}
