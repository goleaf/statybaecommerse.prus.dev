<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\AdminUser;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cached permission mappings to ensure repeatable expectations for each scenario.
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        // Ensure Mockery expectations are always verified to avoid polluting later tests.
        Mockery::close();

        parent::tearDown();
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        // Prepare a payload that touches each fillable attribute so mass assignment remains safe.
        $payload = [
            'name'              => 'Jane Doe',
            'email'             => 'jane@example.com',
            'password'          => 'secret-password',
            'email_verified_at' => now(),
        ];

        // Persist the administrator and refresh the instance to load database managed columns.
        $user = AdminUser::create($payload)->refresh();

        // Verify the primary fields are stored and the password mutator hashes the raw secret.
        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertTrue(Hash::check('secret-password', $user->getAuthPassword()));
    }

    public function test_hidden_attributes_are_not_serialized(): void
    {
        // Create a baseline administrator record through the factory for clarity.
        $user = AdminUser::factory()->create();

        // Convert the user to an array to check serialization behaviour for sensitive columns.
        $userArray = $user->fresh()->toArray();

        // Ensure secret attributes never leak through array/json casting operations.
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_scope_ordered_by_name_sorts_case_insensitively(): void
    {
        // Seed three administrators with deliberately mixed casing to exercise the custom scope.
        $ada = AdminUser::factory()->create(['name' => 'Ada Lovelace']);
        $bert = AdminUser::factory()->create(['name' => 'bert taylor']);
        $zoe = AdminUser::factory()->create(['name' => 'Zoe Alvarez']);

        // Invoke the scope and pluck the names to assert the deterministic alphabetical ordering.
        $orderedNames = AdminUser::orderedByName()->pluck('name')->all();

        // Confirm the sorting ignores casing differences while maintaining stable fallbacks.
        $this->assertSame([
            $ada->name,
            $bert->name,
            $zoe->name,
        ], $orderedNames);
    }

    public function test_can_access_panel_honours_authorization_matrix(): void
    {
        // Toggle the testing bypass flag so the check exercises the permission lookup path.
        $originalSkipSetting = config('authorization.testing.skip_checks');
        config()->set('authorization.testing.skip_checks', false);

        try {
            // Register the permission and create a fresh administrator without any assignments.
            Permission::findOrCreate('panel.access.admin', 'admin');
            $user = AdminUser::factory()->create();
            $panel = Mockery::mock(Panel::class);

            // Without explicit permissions the administrator should be denied.
            $this->assertFalse($user->canAccessPanel($panel));

            // Assign the permission and re-check access to confirm the matrix decision flips.
            $user->givePermissionTo('panel.access.admin');
            $this->assertTrue($user->fresh()->canAccessPanel($panel));
        } finally {
            // Always restore the configuration toggle regardless of assertion outcomes.
            config()->set('authorization.testing.skip_checks', $originalSkipSetting);
        }
    }
}
