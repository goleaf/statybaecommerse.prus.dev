<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Authorization\AuthorizationMatrix;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder for admin authorization system.
 *
 * This seeder creates all the necessary roles and permissions
 * for the admin panel authorization system.
 */
class AdminAuthorizationSeeder extends \Database\Seeders\BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requiredTables = ['permissions', 'roles', 'role_has_permissions'];
        $missingTables = collect($requiredTables)
            ->reject(static fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();

        if ($missingTables !== []) {
            $this->command?->warn(
                'Skipping AdminAuthorizationSeeder because required tables are missing: ' . implode(', ', $missingTables)
            );

            return;
        }

        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each guard
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles();
    }

    /**
     * Create all permissions for each configured guard.
     */
    private function createPermissions(): void
    {
        $permissions = AuthorizationMatrix::allPermissions();
        $guards = AuthorizationMatrix::guardNames();

        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name'       => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }
    }

    /**
     * Create roles and assign permissions.
     */
    private function createRoles(): void
    {
        $guards = AuthorizationMatrix::guardNames();
        $roleDefinitions = AuthorizationMatrix::roles();

        foreach ($guards as $guard) {
            foreach ($roleDefinitions as $roleDefinition) {
                $role = Role::firstOrCreate([
                    'name'       => $roleDefinition['role']->value,
                    'guard_name' => $guard,
                ]);

                // Get permissions for this guard
                $permissions = Permission::where('guard_name', $guard)
                    ->whereIn('name', $roleDefinition['permissions'])
                    ->get();

                // Sync permissions to role
                $role->syncPermissions($permissions);
            }
        }
    }
}
