<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render user resource index page', function () {
    $this->get(UserResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list users', function () {
    $users = User::factory()->count(10)->create();

    Livewire::test(UserResource\Pages\ListUsers::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$users->first()]);
});

it('can create user', function () {
    $newData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'preferred_locale' => 'en',
        'is_active' => true,
    ];

    Livewire::test(UserResource\Pages\CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'preferred_locale' => 'en',
        'is_active' => true,
    ]);
});

it('can validate user creation', function () {
    Livewire::test(UserResource\Pages\CreateUser::class)
        ->fillForm([
            'first_name' => '',
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'first_name' => 'required',
            'email' => 'email',
        ]);
});

it('can edit user', function () {
    $user = User::factory()->create();

    $newData = [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'preferred_locale' => 'lt',
    ];

    Livewire::test(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->first_name->toBe('Updated')
        ->last_name->toBe('Name')
        ->email->toBe('updated@example.com')
        ->preferred_locale->toBe('lt');
});

it('can delete user', function () {
    $user = User::factory()->create();

    Livewire::test(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction('delete');

    $this->assertSoftDeleted($user);
});

it('can assign roles to user', function () {
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create();

    Livewire::test(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'roles' => [$role->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->hasRole('admin'))->toBeTrue();
});

it('can filter users by verification status', function () {
    $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
    $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

    Livewire::test(UserResource\Pages\ListUsers::class)
        ->filterTable('verified')
        ->assertCanSeeTableRecords([$verifiedUser])
        ->assertCanNotSeeTableRecords([$unverifiedUser]);
});

it('can search users by name and email', function () {
    $user1 = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    $user2 = User::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
    ]);

    Livewire::test(UserResource\Pages\ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$user1])
        ->assertCanNotSeeTableRecords([$user2]);
});

it('can bulk activate users', function () {
    $users = User::factory()->count(3)->create(['is_active' => false]);

    Livewire::test(UserResource\Pages\ListUsers::class)
        ->loadTable()
        ->callTableBulkAction('activate', $users);

    foreach ($users as $user) {
        expect($user->refresh()->is_active)->toBeTrue();
    }
});
