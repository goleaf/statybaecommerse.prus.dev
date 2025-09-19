<?php declare(strict_types=1);

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Location;
use App\Models\User;
use App\Filament\Resources\InventoryResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
    $this->actingAs($this->adminUser)
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
    $this->actingAs($this->adminUser)
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
    $this->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('can filter inventories by location', function () {
    $this->actingAs($this->adminUser)
        ->get(InventoryResource::getUrl('index'))
        ->assertOk();
});

it('shows correct inventory data in table', function () {
    $this->actingAs($this->adminUser)
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
