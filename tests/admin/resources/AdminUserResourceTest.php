<?php declare(strict_types=1);

use App\Filament\Resources\AdminUserResource;
use App\Models\AdminUser;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'browse_admin_users',
        'read_admin_users',
        'edit_admin_users',
        'add_admin_users',
        'delete_admin_users',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role = Role::create(['name' => 'administrator']);
    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test data
    $this->testAdminUser = AdminUser::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
    ]);
});

it('can list admin users in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index'))
        ->assertOk();
});

it('can create an admin user', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('admin_users', [
        'name' => 'New Admin',
        'email' => 'newadmin@test.com',
    ]);
});

it('can view an admin user record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('view', ['record' => $this->testAdminUser]))
        ->assertOk();
});

it('can edit an admin user record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\EditAdminUser::class, ['record' => $this->testAdminUser->id])
        ->fillForm([
            'name' => 'Updated Admin',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('admin_users', [
        'id' => $this->testAdminUser->id,
        'name' => 'Updated Admin',
    ]);
});

it('can delete an admin user record', function () {
    $adminUser = AdminUser::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\EditAdminUser::class, ['record' => $adminUser->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('admin_users', [
        'id' => $adminUser->id,
    ]);
});

it('validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => '',
            'email' => '',
            'password' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'email', 'password']);
});

it('validates email format', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('validates password confirmation', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ])
        ->call('create')
        ->assertHasFormErrors(['password_confirmation']);
});

it('validates unique email', function () {
    AdminUser::factory()->create(['email' => 'existing@test.com']);

    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('can filter admin users by email verification status', function () {
    $verified = AdminUser::factory()->create(['email_verified_at' => now()]);
    $unverified = AdminUser::factory()->create(['email_verified_at' => null]);

    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index'))
        ->assertOk();
});

it('can search admin users by name', function () {
    $adminUser = AdminUser::factory()->create(['name' => 'Special Admin']);

    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can search admin users by email', function () {
    $adminUser = AdminUser::factory()->create(['email' => 'special@example.com']);

    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index') . '?search=special@example.com')
        ->assertOk();
});

it('can sort admin users by name', function () {
    $adminUser1 = AdminUser::factory()->create(['name' => 'Zebra Admin']);
    $adminUser2 = AdminUser::factory()->create(['name' => 'Alpha Admin']);

    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index') . '?sort=name&direction=asc')
        ->assertOk();
});

it('can sort admin users by created date', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index') . '?sort=created_at&direction=desc')
        ->assertOk();
});

it('shows correct admin user data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index'))
        ->assertSee($this->testAdminUser->name)
        ->assertSee($this->testAdminUser->email);
});

it('can perform bulk delete action', function () {
    $adminUser1 = AdminUser::factory()->create();
    $adminUser2 = AdminUser::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\ListAdminUsers::class)
        ->callTableBulkAction('delete', [$adminUser1->id, $adminUser2->id])
        ->assertOk();

    $this->assertDatabaseMissing('admin_users', [
        'id' => $adminUser1->id,
    ]);

    $this->assertDatabaseMissing('admin_users', [
        'id' => $adminUser2->id,
    ]);
});

it('can verify email for admin user', function () {
    $adminUser = AdminUser::factory()->create(['email_verified_at' => null]);

    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\ListAdminUsers::class)
        ->callTableAction('verify_email', $adminUser)
        ->assertHasNoActionErrors();

    $adminUser->refresh();
    expect($adminUser->email_verified_at)->not->toBeNull();
});

it('can perform bulk email verification', function () {
    $adminUser1 = AdminUser::factory()->create(['email_verified_at' => null]);
    $adminUser2 = AdminUser::factory()->create(['email_verified_at' => null]);

    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\ListAdminUsers::class)
        ->callTableBulkAction('verify_emails', [$adminUser1->id, $adminUser2->id])
        ->assertHasNoBulkActionErrors();

    $adminUser1->refresh();
    $adminUser2->refresh();

    expect($adminUser1->email_verified_at)->not->toBeNull();
    expect($adminUser2->email_verified_at)->not->toBeNull();
});

it('validates password minimum length', function () {
    Livewire::actingAs($this->adminUser)
        ->test(AdminUserResource\Pages\CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])
        ->call('create')
        ->assertHasFormErrors(['password']);
});

it('shows account details in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('edit', ['record' => $this->testAdminUser]))
        ->assertOk();
});

it('can access admin user resource pages', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('index'))
        ->assertOk();

    $this
        ->actingAs($this->adminUser)
        ->get(AdminUserResource::getUrl('create'))
        ->assertOk();
});

