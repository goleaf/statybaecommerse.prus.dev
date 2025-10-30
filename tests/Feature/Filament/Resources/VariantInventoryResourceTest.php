<?php

declare(strict_types=1);

use App\Filament\Resources\VariantInventoryResource;
use App\Filament\Resources\VariantInventoryResource\Pages\ListVariantInventories;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists variant inventories for admin users', function (): void {
    // Arrange: seed a privileged admin and a single inventory record for visibility assertions.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $inventory = VariantInventory::factory()->create();

    // Act: authenticate as the admin to mount the Filament list page.
    actingAs($admin);

    // Assert: the Livewire table should render the created inventory entry.
    Livewire::test(ListVariantInventories::class)
        ->assertCanSeeTableRecords([$inventory]);
});

it('denies access to non admin users', function (): void {
    // Arrange: generate a regular user without elevated privileges.
    $user = User::factory()->create([
        'is_admin' => false,
    ]);

    // Act: impersonate the user and attempt to open the index route.
    actingAs($user);

    // Assert: the policy gate must block access with a forbidden response.
    $this->get(VariantInventoryResource::getUrl('index'))
        ->assertForbidden();
});

it('filters low stock inventory records', function (): void {
    // Arrange: provision an admin and two contrasting inventory records for filter evaluation.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $lowStockInventory = VariantInventory::factory()->create([
        'available'     => 5,
        'reorder_point' => 10,
    ]);
    $healthyInventory = VariantInventory::factory()->create([
        'available'     => 20,
        'reorder_point' => 10,
    ]);

    // Act: sign in as the admin and apply the predefined low stock table filter.
    actingAs($admin);

    Livewire::test(ListVariantInventories::class)
        ->filterTable('low_stock')
        ->assertCanSeeTableRecords([$lowStockInventory])
        ->assertCanNotSeeTableRecords([$healthyInventory]);
});
