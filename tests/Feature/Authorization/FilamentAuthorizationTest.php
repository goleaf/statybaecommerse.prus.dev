<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->resolveAdminPanel();
});

function createUserWithRole(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role, 'web');
    $user->assignRole($role);

    return $user;
}

it('allows viewers to browse products but hides management actions', function (): void {
    $viewer = createUserWithRole('viewer');
    $product = Product::factory()->create();

    $this->actingAs($viewer);

    $this->get(ProductResource::getUrl('index'))->assertOk();
    $this->get(ProductResource::getUrl('create'))->assertForbidden();

    $component = Livewire::actingAs($viewer)
        ->test(ListProducts::class);

    $component->assertTableActionHidden('edit', $product)
        ->assertTableActionHidden('delete', $product)
        ->assertTableActionVisible('view', $product);

    expect($component->instance()->getCachedHeaderActions())->toBeEmpty();
});

it('allows managers to create and edit products but not delete them', function (): void {
    $manager = createUserWithRole('manager');
    $product = Product::factory()->create();

    $this->actingAs($manager);

    $this->get(ProductResource::getUrl('create'))->assertOk();

    $component = Livewire::actingAs($manager)
        ->test(ListProducts::class);

    $component->assertTableActionVisible('edit', $product)
        ->assertTableActionHidden('delete', $product);

    expect($component->instance()->getCachedHeaderActions())->not->toBeEmpty();
});

it('restricts user management to privileged roles', function (): void {
    $viewer = createUserWithRole('viewer');
    $this->actingAs($viewer);

    $this->get(UserResource::getUrl('index'))->assertForbidden();

    $admin = createUserWithRole('admin');
    $managedUser = User::factory()->create();

    $this->actingAs($admin);

    $this->get(UserResource::getUrl('index'))->assertOk();

    $component = Livewire::actingAs($admin)
        ->test(ListUsers::class);

    $component->assertTableActionVisible('edit', $managedUser)
        ->assertTableActionVisible('delete', $managedUser);

    $manager = createUserWithRole('manager');
    $this->actingAs($manager);

    $this->get(UserResource::getUrl('index'))->assertOk();
    $this->get(UserResource::getUrl('create'))->assertForbidden();

    Livewire::actingAs($manager)
        ->test(ListUsers::class)
        ->assertTableActionHidden('edit', $managedUser)
        ->assertTableActionHidden('delete', $managedUser);
});
