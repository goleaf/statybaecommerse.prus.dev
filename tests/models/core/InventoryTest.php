<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id'   => $product->id,
            'warehouse_id' => $location->id,
        ]);

        $this->assertInstanceOf(Product::class, $inventory->product);
        $this->assertEquals($product->id, $inventory->product->id);
    }

    public function test_inventory_belongs_to_location(): void
    {
        $product = Product::factory()->create();
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id'   => $product->id,
            'warehouse_id' => $location->id,
        ]);

        $this->assertInstanceOf(Location::class, $inventory->warehouse);
        $this->assertEquals($location->id, $inventory->warehouse->id);
        $this->assertTrue($inventory->location->is($location));
    }

    public function test_available_quantity_calculation(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 100,
            'meta' => ['reserved' => 20],
        ]);

        $this->assertEquals(80, $inventory->available_quantity);
    }

    public function test_available_quantity_never_negative(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 10,
            'meta' => ['reserved' => 20],
        ]);

        $this->assertEquals(0, $inventory->available_quantity);
    }

    public function test_is_low_stock(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 5,
            'meta' => ['threshold' => 10],
        ]);

        $this->assertTrue($inventory->isLowStock());
    }

    public function test_is_not_low_stock(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 15,
            'meta' => ['threshold' => 10],
        ]);

        $this->assertFalse($inventory->isLowStock());
    }

    public function test_is_out_of_stock(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 0,
            'meta' => ['reserved' => 0],
        ]);

        $this->assertTrue($inventory->isOutOfStock());
    }

    public function test_is_not_out_of_stock(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => 10,
            'meta' => ['reserved' => 5],
        ]);

        $this->assertFalse($inventory->isOutOfStock());
    }

    public function test_tracked_scope(): void
    {
        Inventory::factory()->create(['meta' => ['is_tracked' => true]]);
        Inventory::factory()->create(['meta' => ['is_tracked' => false]]);

        $trackedInventories = Inventory::tracked()->get();

        $this->assertCount(1, $trackedInventories);
        $this->assertTrue($trackedInventories->first()->is_tracked);
    }

    public function test_low_stock_scope(): void
    {
        Inventory::factory()->create([
            'qty'  => 5,
            'meta' => ['threshold' => 10],
        ]);
        Inventory::factory()->create([
            'qty'  => 15,
            'meta' => ['threshold' => 10],
        ]);

        $lowStockInventories = Inventory::lowStock()->get();

        $this->assertCount(1, $lowStockInventories);
        $this->assertEquals(5, $lowStockInventories->first()->qty);
    }

    public function test_inventory_casts(): void
    {
        $inventory = Inventory::factory()->create([
            'qty'  => '100',
            'meta' => [
                'reserved'   => '20',
                'incoming'   => '50',
                'threshold'  => '10',
                'is_tracked' => '1',
            ],
        ]);

        $this->assertIsInt($inventory->qty);
        $this->assertIsInt($inventory->reserved);
        $this->assertIsInt($inventory->incoming);
        $this->assertIsInt($inventory->threshold);
        $this->assertIsBool($inventory->is_tracked);
    }
}
