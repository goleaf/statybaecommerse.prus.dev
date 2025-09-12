<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\VariantInventory;
use App\Models\ProductVariant;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VariantInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_inventory_belongs_to_variant(): void
    {
        $variant = ProductVariant::factory()->create();
        $location = Location::factory()->create();
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $inventory->variant);
        $this->assertEquals($variant->id, $inventory->variant->id);
    }

    public function test_variant_inventory_belongs_to_location(): void
    {
        $variant = ProductVariant::factory()->create();
        $location = Location::factory()->create();
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
        ]);

        $this->assertInstanceOf(Location::class, $inventory->location);
        $this->assertEquals($location->id, $inventory->location->id);
    }

    public function test_available_stock_calculation(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 100,
            'reserved' => 20,
        ]);

        $this->assertEquals(80, $inventory->available_stock);
    }

    public function test_available_stock_never_negative(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 10,
            'reserved' => 20,
        ]);

        $this->assertEquals(0, $inventory->available_stock);
    }

    public function test_is_low_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 5,
            'threshold' => 10,
        ]);

        $this->assertTrue($inventory->isLowStock());
    }

    public function test_is_not_low_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 15,
            'threshold' => 10,
        ]);

        $this->assertFalse($inventory->isLowStock());
    }

    public function test_is_out_of_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 0,
            'reserved' => 0,
        ]);

        $this->assertTrue($inventory->isOutOfStock());
    }

    public function test_is_not_out_of_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => 10,
            'reserved' => 5,
        ]);

        $this->assertFalse($inventory->isOutOfStock());
    }

    public function test_tracked_scope(): void
    {
        VariantInventory::factory()->create(['is_tracked' => true]);
        VariantInventory::factory()->create(['is_tracked' => false]);

        $trackedInventories = VariantInventory::tracked()->get();

        $this->assertCount(1, $trackedInventories);
        $this->assertTrue($trackedInventories->first()->is_tracked);
    }

    public function test_low_stock_scope(): void
    {
        VariantInventory::factory()->create([
            'stock' => 5,
            'threshold' => 10,
        ]);
        VariantInventory::factory()->create([
            'stock' => 15,
            'threshold' => 10,
        ]);

        $lowStockInventories = VariantInventory::lowStock()->get();

        $this->assertCount(1, $lowStockInventories);
        $this->assertEquals(5, $lowStockInventories->first()->stock);
    }

    public function test_out_of_stock_scope(): void
    {
        VariantInventory::factory()->create(['stock' => 0]);
        VariantInventory::factory()->create(['stock' => 10]);

        $outOfStockInventories = VariantInventory::outOfStock()->get();

        $this->assertCount(1, $outOfStockInventories);
        $this->assertEquals(0, $outOfStockInventories->first()->stock);
    }

    public function test_variant_inventory_casts(): void
    {
        $inventory = VariantInventory::factory()->create([
            'stock' => '100',
            'reserved' => '20',
            'incoming' => '50',
            'threshold' => '10',
            'is_tracked' => '1',
        ]);

        $this->assertIsInt($inventory->stock);
        $this->assertIsInt($inventory->reserved);
        $this->assertIsInt($inventory->incoming);
        $this->assertIsInt($inventory->threshold);
        $this->assertIsBool($inventory->is_tracked);
    }
}
