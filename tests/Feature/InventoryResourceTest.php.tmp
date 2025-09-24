<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

class InventoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_inventories(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved' => 10,
            'incoming' => 5,
            'threshold' => 20,
            'is_tracked' => true,
        ]);

        $this->get('/admin/inventories')
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($location->name)
            ->assertSee('100')
            ->assertSee('10')
            ->assertSee('5');
    }

    public function test_can_create_inventory(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $this->get('/admin/inventories/create')
            ->assertOk();

        $this->post('/admin/inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 50,
            'reserved' => 5,
            'incoming' => 10,
            'threshold' => 15,
            'is_tracked' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 50,
            'reserved' => 5,
            'incoming' => 10,
            'threshold' => 15,
            'is_tracked' => true,
        ]);
    }

    public function test_can_view_inventory(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
        ]);

        $this->get("/admin/inventories/{$inventory->id}")
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($location->name);
    }

    public function test_can_edit_inventory(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
        ]);

        $this->get("/admin/inventories/{$inventory->id}/edit")
            ->assertOk();

        $this->put("/admin/inventories/{$inventory->id}", [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 75,
            'reserved' => 8,
            'incoming' => 12,
            'threshold' => 25,
            'is_tracked' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 75,
            'reserved' => 8,
            'incoming' => 12,
            'threshold' => 25,
            'is_tracked' => false,
        ]);
    }

    public function test_can_delete_inventory(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
        ]);

        $this->delete("/admin/inventories/{$inventory->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('inventories', [
            'id' => $inventory->id,
        ]);
    }

    public function test_can_filter_inventories_by_product(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);
        $location = Location::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product1->id,
            'location_id' => $location->id,
        ]);

        Inventory::factory()->create([
            'product_id' => $product2->id,
            'location_id' => $location->id,
        ]);

        $this->get('/admin/inventories?product='.$product1->id)
            ->assertOk()
            ->assertSee('Product 1')
            ->assertDontSee('Product 2');
    }

    public function test_can_filter_inventories_by_location(): void
    {
        $product = Product::factory()->create();
        $location1 = Location::factory()->create(['name' => 'Location 1']);
        $location2 = Location::factory()->create(['name' => 'Location 2']);

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location1->id,
        ]);

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location2->id,
        ]);

        $this->get('/admin/inventories?location='.$location1->id)
            ->assertOk()
            ->assertSee('Location 1')
            ->assertDontSee('Location 2');
    }

    public function test_can_filter_inventories_by_stock_status(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        // Out of stock
        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 0,
            'reserved' => 0,
        ]);

        // Low stock
        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 10,
            'reserved' => 5,
            'threshold' => 20,
        ]);

        // In stock
        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved' => 10,
            'threshold' => 20,
        ]);

        $this->get('/admin/inventories?stock_status=out_of_stock')
            ->assertOk()
            ->assertSee('0');

        $this->get('/admin/inventories?stock_status=low_stock')
            ->assertOk()
            ->assertSee('10');

        $this->get('/admin/inventories?stock_status=in_stock')
            ->assertOk()
            ->assertSee('100');
    }

    public function test_can_filter_inventories_by_tracking_status(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'is_tracked' => true,
        ]);

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'is_tracked' => false,
        ]);

        $this->get('/admin/inventories?is_tracked=1')
            ->assertOk();

        $this->get('/admin/inventories?is_tracked=0')
            ->assertOk();
    }

    public function test_can_bulk_adjust_stock(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory1 = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 50,
        ]);

        $inventory2 = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 75,
        ]);

        $this->post('/admin/inventories/bulk-adjust-stock', [
            'records' => [$inventory1->id, $inventory2->id],
            'quantity' => 100,
            'reserved' => 10,
            'incoming' => 5,
            'threshold' => 20,
        ])->assertRedirect();

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
    }

    public function test_can_bulk_add_stock(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 50,
        ]);

        $this->post('/admin/inventories/bulk-add-stock', [
            'records' => [$inventory->id],
            'add_quantity' => 25,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 75,
        ]);
    }

    public function test_can_bulk_toggle_tracking(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'is_tracked' => true,
        ]);

        $this->post('/admin/inventories/bulk-toggle-tracking', [
            'records' => [$inventory->id],
            'is_tracked' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'is_tracked' => false,
        ]);
    }

    public function test_inventory_available_quantity_calculation(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved' => 20,
        ]);

        $this->assertEquals(80, $inventory->available_quantity);
    }

    public function test_inventory_stock_status_calculations(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        // Out of stock
        $inventory1 = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 0,
            'reserved' => 0,
            'threshold' => 10,
        ]);

        // Low stock
        $inventory2 = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 15,
            'reserved' => 5,
            'threshold' => 20,
        ]);

        // In stock
        $inventory3 = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved' => 10,
            'threshold' => 20,
        ]);

        $this->assertTrue($inventory1->isOutOfStock());
        $this->assertFalse($inventory1->isLowStock());

        $this->assertFalse($inventory2->isOutOfStock());
        $this->assertTrue($inventory2->isLowStock());

        $this->assertFalse($inventory3->isOutOfStock());
        $this->assertFalse($inventory3->isLowStock());
    }
}
