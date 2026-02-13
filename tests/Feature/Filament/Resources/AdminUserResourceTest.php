<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminUserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');
    }

    public function test_admin_user_can_be_created_from_filament_resource(): void
    {
        $email = 'filament-admin-create-' . uniqid('', true) . '@example.test';

        Livewire::test(CreateAdminUser::class)
            ->fillForm([
                'name'     => 'Panel Admin',
                'email'    => $email,
                'password' => 'SecurePass123!',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdAdmin = AdminUser::query()->where('email', $email)->first();

        $this->assertNotNull($createdAdmin);
        $this->assertTrue(Hash::check('SecurePass123!', (string) $createdAdmin->password));
    }

    public function test_create_admin_user_validates_password_strength_instead_of_throwing_server_error(): void
    {
        $email = 'filament-admin-weak-' . uniqid('', true) . '@example.test';

        Livewire::test(CreateAdminUser::class)
            ->fillForm([
                'name'     => 'Weak Password Admin',
                'email'    => $email,
                'password' => 'weakpass',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);

        $this->assertDatabaseMissing('admin_users', [
            'email' => $email,
        ]);
    }

    public function test_admin_user_password_can_be_updated_from_filament_resource(): void
    {
        $targetAdmin = AdminUser::factory()->withPassword('OldSecurePass123!')->create();
        $oldPasswordHash = (string) $targetAdmin->password;

        Livewire::test(EditAdminUser::class, [
            'record' => $targetAdmin->getRouteKey(),
        ])
            ->fillForm([
                'name'     => (string) $targetAdmin->name,
                'email'    => (string) $targetAdmin->email,
                'password' => 'NewSecurePass123!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $targetAdmin->refresh();

        $this->assertNotSame($oldPasswordHash, $targetAdmin->password);
        $this->assertTrue(Hash::check('NewSecurePass123!', (string) $targetAdmin->password));
    }
}
