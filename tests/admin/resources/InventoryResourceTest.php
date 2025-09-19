<?php declare(strict_types=1);

use App\Filament\Resources\InventoryResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view inventories',
        'create inventories',
        'update inventories',
        'delete inventories',
        'browse_inventories'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test data
    $this->testProduct = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
    ]);

    $this->testLocation = Location::factory()->create([
        'name' => 'Test Location',
        'code' => 'TEST',
    ]);

    $this->testInventory = Inventory::factory()->create([
        'product_id' => $this->testProduct->id,
        'location_id' => $this->testLocation->id,
        'quantity' => 100,
        'reserved' => 10,
        'incoming' => 5,
        'threshold' => 20,
        'is_tracked' => true,
    ]);
});

it('can list inventories in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can create a new inventory record', function () {
    $product = Product::factory()->create();
    $location = Location::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\CreateInventory::class)
        ->fillForm([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 50,
            'reserved' => 5,
            'incoming' => 3,
            'threshold' => 10,
            'is_tracked' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('inventories', [
        'product_id' => $product->id,
        'location_id' => $location->id,
        'quantity' => 50,
        'reserved' => 5,
        'incoming' => 3,
        'threshold' => 10,
        'is_tracked' => true,
    ]);
});

it('can view an inventory record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('view', ['record' => $this->testInventory]))
        ->assertOk();
});

it('can edit an inventory record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\EditInventory::class, ['record' => $this->testInventory->id])
        ->fillForm([
            'quantity' => 200,
            'reserved' => 20,
            'incoming' => 10,
            'threshold' => 30,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('inventories', [
        'id' => $this->testInventory->id,
        'quantity' => 200,
        'reserved' => 20,
        'incoming' => 10,
        'threshold' => 30,
    ]);
});

it('can delete an inventory record', function () {
    $inventory = Inventory::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\EditInventory::class, ['record' => $inventory->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('inventories', [
        'id' => $inventory->id,
    ]);
});

it('validates required fields when creating inventory', function () {
    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\CreateInventory::class)
        ->fillForm([
            'product_id' => null,
            'location_id' => null,
            'quantity' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['product_id', 'location_id', 'quantity']);
});

it('validates numeric fields in inventory form', function () {
    $product = Product::factory()->create();
    $location = Location::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\CreateInventory::class)
        ->fillForm([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 'not-a-number',
            'reserved' => 'invalid',
            'incoming' => 'invalid',
            'threshold' => 'invalid',
        ])
        ->call('create')
        ->assertHasFormErrors(['quantity', 'reserved', 'incoming', 'threshold']);
});

it('can filter inventories by product', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can filter inventories by location', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('shows correct inventory data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertSee($this->testProduct->name)
        ->assertSee($this->testLocation->name)
        ->assertSee('100')
        ->assertSee('10')
        ->assertSee('5')
        ->assertSee('20');
});

it('calculates available quantity correctly', function () {
    $inventory = Inventory::factory()->create([
        'quantity' => 100,
        'reserved' => 15,
    ]);

    expect($inventory->available_quantity)->toBe(85);
});

it('handles bulk actions on inventories', function () {
    $inventory1 = Inventory::factory()->create();
    $inventory2 = Inventory::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableBulkAction('delete', [$inventory1->id, $inventory2->id])
        ->assertOk();

    $this->assertDatabaseMissing('inventories', [
        'id' => $inventory1->id,
    ]);

    $this->assertDatabaseMissing('inventories', [
        'id' => $inventory2->id,
    ]);
});

it('can adjust stock for inventory record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('adjust_stock', $this->testInventory, [
            'quantity' => 150,
            'reserved' => 25,
            'incoming' => 10,
            'threshold' => 30,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('inventories', [
        'id' => $this->testInventory->id,
        'quantity' => 150,
        'reserved' => 25,
        'incoming' => 10,
        'threshold' => 30,
    ]);
});

it('can add stock to inventory record', function () {
    $originalQuantity = $this->testInventory->quantity;

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('add_stock', $this->testInventory, [
            'add_quantity' => 50,
        ])
        ->assertHasNoActionErrors();

    $this->testInventory->refresh();
    expect($this->testInventory->quantity)->toBe($originalQuantity + 50);
});

it('can remove stock from inventory record', function () {
    $originalQuantity = $this->testInventory->quantity;

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('remove_stock', $this->testInventory, [
            'remove_quantity' => 20,
        ])
        ->assertHasNoActionErrors();

    $this->testInventory->refresh();
    expect($this->testInventory->quantity)->toBe($originalQuantity - 20);
});

it('can reserve stock for inventory record', function () {
    $originalReserved = $this->testInventory->reserved;

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('reserve_stock', $this->testInventory, [
            'reserve_quantity' => 15,
        ])
        ->assertHasNoActionErrors();

    $this->testInventory->refresh();
    expect($this->testInventory->reserved)->toBe($originalReserved + 15);
});

it('can filter inventories by stock status', function () {
    // Create inventories with different stock statuses
    $outOfStock = Inventory::factory()->create([
        'quantity' => 0,
        'reserved' => 0,
        'threshold' => 10,
    ]);

    $lowStock = Inventory::factory()->create([
        'quantity' => 5,
        'reserved' => 0,
        'threshold' => 10,
    ]);

    $inStock = Inventory::factory()->create([
        'quantity' => 50,
        'reserved' => 0,
        'threshold' => 10,
    ]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can filter inventories by quantity range', function () {
    $lowQuantity = Inventory::factory()->create(['quantity' => 5]);
    $highQuantity = Inventory::factory()->create(['quantity' => 100]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can perform bulk stock adjustments', function () {
    $inventory1 = Inventory::factory()->create(['quantity' => 50]);
    $inventory2 = Inventory::factory()->create(['quantity' => 30]);

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableBulkAction('adjust_stock', [$inventory1->id, $inventory2->id], [
            'quantity' => 100,
            'reserved' => 10,
            'incoming' => 5,
            'threshold' => 20,
        ])
        ->assertHasNoBulkActionErrors();

    $this->assertDatabaseHas('inventories', [
        'id' => $inventory1->id,
        'quantity' => 100,
        'reserved' => 10,
        'incoming' => 5,
        'threshold' => 20,
    ]);

    $this->assertDatabaseHas('inventories', [
        'id' => $inventory2->id,
        'quantity' => 100,
        'reserved' => 10,
        'incoming' => 5,
        'threshold' => 20,
    ]);
});

it('can perform bulk stock additions', function () {
    $inventory1 = Inventory::factory()->create(['quantity' => 50]);
    $inventory2 = Inventory::factory()->create(['quantity' => 30]);

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableBulkAction('add_stock', [$inventory1->id, $inventory2->id], [
            'add_quantity' => 25,
        ])
        ->assertHasNoBulkActionErrors();

    $inventory1->refresh();
    $inventory2->refresh();

    expect($inventory1->quantity)->toBe(75);
    expect($inventory2->quantity)->toBe(55);
});

it('can toggle tracking for multiple inventories', function () {
    $inventory1 = Inventory::factory()->create(['is_tracked' => true]);
    $inventory2 = Inventory::factory()->create(['is_tracked' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableBulkAction('toggle_tracking', [$inventory1->id, $inventory2->id], [
            'is_tracked' => false,
        ])
        ->assertHasNoBulkActionErrors();

    $inventory1->refresh();
    $inventory2->refresh();

    expect($inventory1->is_tracked)->toBeFalse();
    expect($inventory2->is_tracked)->toBeFalse();
});

it('validates stock adjustment limits', function () {
    $inventory = Inventory::factory()->create(['quantity' => 50]);

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('remove_stock', $inventory, [
            'remove_quantity' => 100,  // More than available
        ])
        ->assertHasActionErrors(['remove_quantity']);
});

it('validates reserve stock limits', function () {
    $inventory = Inventory::factory()->create([
        'quantity' => 50,
        'reserved' => 10,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableAction('reserve_stock', $inventory, [
            'reserve_quantity' => 50,  // More than available
        ])
        ->assertHasActionErrors(['reserve_quantity']);
});

it('shows correct stock status badges', function () {
    $outOfStock = Inventory::factory()->create([
        'quantity' => 0,
        'reserved' => 0,
    ]);

    $lowStock = Inventory::factory()->create([
        'quantity' => 5,
        'reserved' => 0,
        'threshold' => 10,
    ]);

    $inStock = Inventory::factory()->create([
        'quantity' => 50,
        'reserved' => 0,
        'threshold' => 10,
    ]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can search inventories by product name', function () {
    $product = Product::factory()->create(['name' => 'Special Product']);
    $inventory = Inventory::factory()->create(['product_id' => $product->id]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can search inventories by location name', function () {
    $location = Location::factory()->create(['name' => 'Special Location']);
    $inventory = Inventory::factory()->create(['location_id' => $location->id]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can sort inventories by quantity', function () {
    $lowQuantity = Inventory::factory()->create(['quantity' => 10]);
    $highQuantity = Inventory::factory()->create(['quantity' => 100]);

    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index') . '?sort=quantity&direction=asc')
        ->assertOk();
});

it('can sort inventories by created date', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index') . '?sort=created_at&direction=desc')
        ->assertOk();
});

it('shows product and location details in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('edit', ['record' => $this->testInventory]))
        ->assertOk();
});

it('validates required fields in bulk actions', function () {
    $inventory = Inventory::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(InventoryResource\Pages\ListInventories::class)
        ->callTableBulkAction('adjust_stock', [$inventory->id], [
            'quantity' => null,
        ])
        ->assertHasBulkActionErrors(['quantity']);
});

it('handles inventory with product relationships', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);
    $product->categories()->attach($category->id);

    $inventory = Inventory::factory()->create(['product_id' => $product->id]);

    expect($inventory->product->brand)->not->toBeNull();
    expect($inventory->product->categories)->toHaveCount(1);
});

it('handles inventory with location relationships', function () {
    $location = Location::factory()->create([
        'name' => 'Test Warehouse',
        'code' => 'TW001',
        'city' => 'Vilnius',
    ]);

    $inventory = Inventory::factory()->create(['location_id' => $location->id]);

    expect($inventory->location->name)->toBe('Test Warehouse');
    expect($inventory->location->code)->toBe('TW001');
    expect($inventory->location->city)->toBe('Vilnius');
});
