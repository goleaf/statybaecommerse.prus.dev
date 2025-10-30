<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Support\Authorization\AuthorizationMatrix;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AdminAuthorizationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_permissions_for_every_configured_guard(): void
    {
        // Arrange: expose a third guard to confirm the seeder loops across all configured guards.
        config(['authorization.guards' => ['admin', 'web', 'sanctum']]);

        // Act: execute the seeder so permission rows are created for each guard context.
        $this->seed(AdminAuthorizationSeeder::class);

        // Assert: verify the canonical panel access permission exists for each guard defined in the matrix.
        foreach (AuthorizationMatrix::guardNames() as $guard) {
            $message = sprintf('Expected panel access permission to be seeded for the [%s] guard.', $guard);

            $this->assertTrue(
                Permission::query()
                    ->where('guard_name', $guard)
                    ->where('name', AuthorizationMatrix::ability('panel', 'access'))
                    ->exists(),
                $message
            );
        }
    }

    public function test_roles_receive_guard_specific_permission_assignments(): void
    {
        // Arrange: include an API-style guard so every role is provisioned for more than the default contexts.
        config(['authorization.guards' => ['admin', 'web', 'sanctum']]);

        // Act: run the authorization seeder to synchronise roles and permissions per guard.
        $this->seed(AdminAuthorizationSeeder::class);

        // Assert: inspect each configured role to ensure the guard-specific assignments match the matrix definition.
        foreach (AuthorizationMatrix::roles() as ['role' => $role, 'permissions' => $expectedPermissions]) {
            foreach (AuthorizationMatrix::guardNames() as $guard) {
                $roleModel = Role::query()
                    ->where('name', $role->value)
                    ->where('guard_name', $guard)
                    ->first();

                $this->assertNotNull(
                    $roleModel,
                    sprintf('Role [%s] should be created for the [%s] guard.', $role->value, $guard)
                );

                // Sort both lists so comparisons remain stable regardless of insertion order.
                $expected = $expectedPermissions;
                sort($expected);

                $actual = $roleModel->permissions
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all();

                $this->assertSame(
                    $expected,
                    $actual,
                    sprintf('Role [%s] should mirror the matrix permissions for the [%s] guard.', $role->value, $guard)
                );
            }
        }
    }
}
