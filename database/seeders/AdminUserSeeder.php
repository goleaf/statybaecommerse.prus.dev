<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic admin role and permissions first
        $this->createBasicPermissions();
        $this->createAdminRole();

        // Then create the super admin user
        $this->createSuperAdmin();

        $this->command->info('✅ Super admin user created successfully!');
    }

    private function createBasicPermissions(): void
    {
        $permissions = [
            'view_admin_panel',
            'view_dashboard',
            'manage_all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function createAdminRole(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
    }

    private function createSuperAdmin(): void
    {
        // Create the super admin user with your specified credentials
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
                'is_active' => true,
            ]
        );

        // Assign administrator role
        if (!$superAdmin->hasRole('administrator')) {
            $superAdmin->assignRole('administrator');
        }

        $this->command->info('👤 Super Admin created: admin@example.com / password');
    }
}
