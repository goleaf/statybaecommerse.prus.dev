<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AdminUserResource\Pages\CreateAdminUser;
use App\Filament\Resources\AdminUserResource\Pages\EditAdminUser;
use App\Filament\Resources\AdminUserResource\Pages\ListAdminUsers;
use App\Filament\Resources\AdminUserResource\Pages\ViewAdminUser;
use App\Models\AdminUser;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminUserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we use the admin panel and guard for Filament
        Filament::setCurrentPanel('admin');
        Config::set('auth.defaults.guard', 'admin');

        // Create a test admin user for authentication
        $this->adminUser = AdminUser::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_can_list_admin_users(): void
    {
        // Arrange
        $adminUsers = AdminUser::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->assertCanSeeTableRecords($adminUsers);
    }

    public function test_can_create_admin_user(): void
    {
        // Arrange
        $adminUserData = [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAdminUser::class)
            ->fillForm($adminUserData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('admin_users', [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
        ]);
    }

    public function test_can_edit_admin_user(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create();
        $newName = 'Updated Name';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAdminUser::class, ['record' => $adminUser->id])
            ->fillForm(['name' => $newName])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('admin_users', [
            'id' => $adminUser->id,
            'name' => $newName,
        ]);
    }

    public function test_can_view_admin_user(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewAdminUser::class, ['record' => $adminUser->id])
            ->assertSee($adminUser->name);
    }

    public function test_can_filter_admin_users_by_email_verification(): void
    {
        // Arrange
        $verifiedUsers = AdminUser::factory()->count(3)->create(['email_verified_at' => now()]);
        $unverifiedUsers = AdminUser::factory()->count(2)->create(['email_verified_at' => null]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->filterTable('email_verified', 'verified')
            ->assertCanSeeTableRecords($verifiedUsers)
            ->assertCanNotSeeTableRecords($unverifiedUsers);
    }

    public function test_can_verify_admin_user_email(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create(['email_verified_at' => null]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableAction('verify_email', $adminUser)
            ->assertNotified();

        $this->assertDatabaseHas('admin_users', [
            'id' => $adminUser->id,
            'email_verified_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_can_send_verification_email(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create(['email_verified_at' => null]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableAction('send_verification', $adminUser)
            ->assertNotified();
    }

    public function test_can_bulk_verify_admin_user_emails(): void
    {
        // Arrange
        $adminUsers = AdminUser::factory()->count(3)->create(['email_verified_at' => null]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableBulkAction('verify_emails', $adminUsers)
            ->assertNotified();

        foreach ($adminUsers as $adminUser) {
            $this->assertDatabaseHas('admin_users', [
                'id' => $adminUser->id,
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function test_can_bulk_send_verification_emails(): void
    {
        // Arrange
        $adminUsers = AdminUser::factory()->count(3)->create(['email_verified_at' => null]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableBulkAction('send_verifications', $adminUsers)
            ->assertNotified();
    }

    public function test_can_search_admin_users(): void
    {
        // Arrange
        $searchableUser = AdminUser::factory()->create(['name' => 'Unique Name']);
        $otherUsers = AdminUser::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->searchTable('Unique Name')
            ->assertCanSeeTableRecords([$searchableUser])
            ->assertCanNotSeeTableRecords($otherUsers);
    }

    public function test_can_sort_admin_users_by_created_at(): void
    {
        // Arrange
        $oldUser = AdminUser::factory()->create(['created_at' => now()->subDay()]);
        $newUser = AdminUser::factory()->create(['created_at' => now()]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newUser, $oldUser], inOrder: true);
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAdminUser::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['name', 'email', 'password']);
    }

    public function test_validates_password_confirmation_on_create(): void
    {
        // Arrange
        $adminUserData = [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAdminUser::class)
            ->fillForm($adminUserData)
            ->call('create')
            ->assertHasFormErrors(['password_confirmation']);
    }

    public function test_validates_email_uniqueness_on_create(): void
    {
        // Arrange
        $existingUser = AdminUser::factory()->create(['email' => 'existing@example.com']);
        $adminUserData = [
            'name' => 'Test Admin',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAdminUser::class)
            ->fillForm($adminUserData)
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_can_delete_admin_user(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableAction('delete', $adminUser)
            ->assertNotified();

        $this->assertDatabaseMissing('admin_users', ['id' => $adminUser->id]);
    }

    public function test_can_bulk_delete_admin_users(): void
    {
        // Arrange
        $adminUsers = AdminUser::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->callTableBulkAction('delete', $adminUsers)
            ->assertNotified();

        foreach ($adminUsers as $adminUser) {
            $this->assertDatabaseMissing('admin_users', ['id' => $adminUser->id]);
        }
    }

    public function test_can_filter_recent_admin_users(): void
    {
        // Arrange
        $recentUsers = AdminUser::factory()->count(3)->create(['created_at' => now()->subDays(10)]);
        $oldUsers = AdminUser::factory()->count(2)->create(['created_at' => now()->subDays(40)]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAdminUsers::class)
            ->filterTable('recent')
            ->assertCanSeeTableRecords($recentUsers)
            ->assertCanNotSeeTableRecords($oldUsers);
    }

    public function test_password_is_not_required_on_edit(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAdminUser::class, ['record' => $adminUser->id])
            ->fillForm(['name' => 'Updated Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('admin_users', [
            'id' => $adminUser->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_update_password_on_edit(): void
    {
        // Arrange
        $adminUser = AdminUser::factory()->create();
        $newPassword = 'newpassword123';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAdminUser::class, ['record' => $adminUser->id])
            ->fillForm([
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Verify password was updated by checking if we can authenticate
        $this->assertTrue(
            \Hash::check($newPassword, $adminUser->fresh()->password)
        );
    }
}
