<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

        $permissions = Permission::factory()
            ->count(50)
            ->create(['guard_name' => 'web']);

        $superAdminRole->syncPermissions($permissions);

        $admin = User::factory()
            ->admin()
            ->create([
                'email' => 'admin@example.com',
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
            ]);

        $admin->syncRoles([$superAdminRole]);

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: admin@example.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('🎭 Role: super-admin with '.$permissions->count().' permissions');
    }
}
