<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WishlistRelationManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can list users', function () {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can create a user', function () {
    $newUserData = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'first_name' => $newUserData->first_name,
            'last_name' => $newUserData->last_name,
            'name' => $newUserData->name,
            'email' => $newUserData->email,
            'password' => 'password123',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'first_name' => $newUserData->first_name,
        'last_name' => $newUserData->last_name,
        'email' => $newUserData->email,
    ]);
});

it('can view a user', function () {
    $user = User::factory()->create();

    Livewire::test(ViewUser::class, ['record' => $user->id])
        ->assertOk();
});

it('can edit a user', function () {
    $user = User::factory()->create();
    $newUserData = User::factory()->make();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'first_name' => $newUserData->first_name,
            'last_name' => $newUserData->last_name,
            'email' => $newUserData->email,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'first_name' => $newUserData->first_name,
        'last_name' => $newUserData->last_name,
        'email' => $newUserData->email,
    ]);
});

it('can delete a user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->callAction('delete')
        ->assertOk();

    assertDatabaseMissing(User::class, [
        'id' => $user->id,
    ]);
});

it('can activate users in bulk', function () {
    $users = User::factory()->count(3)->create(['is_active' => false]);

    Livewire::test(ListUsers::class)
        ->callTableBulkAction('activate', $users)
        ->assertNotified();

    foreach ($users as $user) {
        assertDatabaseHas(User::class, [
            'id' => $user->id,
            'is_active' => true,
        ]);
    }
});

it('can deactivate users in bulk', function () {
    $users = User::factory()->count(3)->create(['is_active' => true]);

    Livewire::test(ListUsers::class)
        ->callTableBulkAction('deactivate', $users)
        ->assertNotified();

    foreach ($users as $user) {
        assertDatabaseHas(User::class, [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }
});

it('can load addresses relation manager', function () {
    $user = User::factory()->create();

    Livewire::test(AddressesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertOk();
});

it('can load orders relation manager', function () {
    $user = User::factory()->create();

    Livewire::test(OrdersRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertOk();
});

it('can load reviews relation manager', function () {
    $user = User::factory()->create();

    Livewire::test(ReviewsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertOk();
});

it('can load wishlist relation manager', function () {
    $user = User::factory()->create();

    Livewire::test(WishlistRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertOk();
});
