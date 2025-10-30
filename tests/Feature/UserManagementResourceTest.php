<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserManagementResource\Pages\CreateUser;
use App\Filament\Resources\UserManagementResource\Pages\EditUser;
use App\Filament\Resources\UserManagementResource\Pages\ListUsers;
use App\Filament\Resources\UserManagementResource\Pages\ViewUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Livewire\Livewire;
use Tests\TestCase;

final class UserManagementResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel is registered so page lookups and navigation behave like production.
        $this->resolveAdminPanel();

        // Provision the permissions matrix so assigning the super_admin role mirrors the live configuration.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Promote a freshly created user to super_admin for unrestricted access to the management screens.
        $this->adminUser = User::factory()->create([
            'email' => 'admin-user-management@example.test',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_active_and_inactive_users(): void
    {
        // Create both active and inactive users so the query scope removal can be asserted explicitly.
        $activeUser = User::factory()->create(['name' => 'Active Person', 'is_active' => true]);
        $inactiveUser = User::factory()->create(['name' => 'Inactive Person', 'is_active' => false]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$activeUser, $inactiveUser]);
    }

    public function test_admin_can_view_user_details(): void
    {
        // Persist a user record so the management view has data to surface.
        $user = User::factory()->create([
            'name' => 'Viewed Person',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewUser::class, ['record' => $user->getKey()])
            ->assertFormSet([
                'name'             => 'Viewed Person',
                'email'            => $user->email,
                'preferred_locale' => $user->preferred_locale,
            ]);
    }

    public function test_admin_can_create_user_via_management_resource(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateUser::class)
            ->fillForm([
                'name'              => 'New Admin User',
                'email'             => 'new-admin@example.test',
                'password'          => 'secret-pass',
                'preferred_locale'  => 'en',
                'is_active'         => true,
                'permissions_matrix' => Arr::wrap([]),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.test',
            'name'  => 'New Admin User',
        ]);
    }

    public function test_admin_can_edit_user_via_management_resource(): void
    {
        // Seed a baseline user so we can verify the edit form hydrates and persists changes.
        $user = User::factory()->create([
            'name' => 'Editable Person',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name'             => 'Updated Person',
                'preferred_locale' => 'en',
                'is_active'        => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated Person',
        ]);
    }

    public function test_bulk_deactivate_action_updates_selected_users(): void
    {
        // Prepare a handful of active users so the bulk action has multiple records to operate on.
        $users = User::factory()->count(3)->create([
            'is_active' => true,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->call('loadTable')
            ->callTableBulkAction('deactivate', $users)
            ->assertNotified();

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', [
                'id'        => $user->id,
                'is_active' => false,
            ]);
        }
    }
}
