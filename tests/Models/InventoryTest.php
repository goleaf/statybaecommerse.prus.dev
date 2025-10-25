<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ensure the inventory model remains feature-complete and regression free.
 */
final class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_is_explicit(): void
    {
        // Verifies that mass-assignment remains locked down to the documented attributes.
        $inventory = new Inventory();

        $this->assertSame([
            'product_id',
            'location_id',
            'quantity',
            'reserved',
            'incoming',
            'threshold',
            'is_tracked',
        ], $inventory->getFillable());
    }

    public function test_casts_transform_numeric_fields(): void
    {
        // Use the factory to persist values that require casting to integers and booleans.
        $inventory = Inventory::factory()->create([
            'quantity' => '10',
            'reserved' => '2',
            'incoming' => '5',
            'threshold' => '3',
            'is_tracked' => '1',
        ]);

        $this->assertIsInt($inventory->quantity);
        $this->assertIsInt($inventory->reserved);
        $this->assertIsInt($inventory->incoming);
        $this->assertIsInt($inventory->threshold);
        $this->assertIsBool($inventory->is_tracked);
    }

    public function test_relationships_link_to_product_and_location(): void
    {
        // Confirm the belongsTo relationships stay wired correctly for reporting queries.
        $product = Product::factory()->create();
        $location = Location::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($inventory->product->is($product));
        $this->assertTrue($inventory->location->is($location));
    }

    public function test_available_quantity_attribute_never_drops_below_zero(): void
    {
        // Ensure subtraction handles over-reservation gracefully.
        $inventory = Inventory::factory()->create([
            'quantity' => 4,
            'reserved' => 10,
        ]);

        $this->assertSame(0, $inventory->available_quantity);
    }

    public function test_product_name_accessor_handles_missing_relation(): void
    {
        // When the relation is not loaded or missing, the accessor should degrade gracefully.
        $inventory = Inventory::factory()->make();
        $inventory->setRelation('product', null);

        $this->assertSame('', $inventory->product_name);
    }

    public function test_tracked_scope_filters_only_tracked_records(): void
    {
        // Seed a tracked and an untracked inventory to ensure the scope filters correctly.
        Inventory::factory()->create(['is_tracked' => true]);
        Inventory::factory()->create(['is_tracked' => false]);

        $results = Inventory::tracked()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_tracked);
    }

    public function test_low_stock_scope_honours_threshold_and_reserved_stock(): void
    {
        // Build scenarios that verify low stock depends on both the threshold and reserved units.
        Inventory::factory()->create([
            'quantity' => 10,
            'reserved' => 6,
            'threshold' => 5,
        ]);

        Inventory::factory()->create([
            'quantity' => 10,
            'reserved' => 2,
            'threshold' => 5,
        ]);

        $results = Inventory::lowStock()->get();

        $this->assertCount(1, $results);
        $this->assertSame(4, $results->first()->quantity - $results->first()->reserved);
    }

    public function test_low_stock_detection_requires_positive_threshold(): void
    {
        // Explicitly set a zero threshold to confirm the helper ignores such configurations.
        $inventory = Inventory::factory()->create([
            'quantity' => 3,
            'reserved' => 0,
            'threshold' => 0,
        ]);

        $this->assertFalse($inventory->isLowStock());
    }

    public function test_default_attributes_prevent_null_math(): void
    {
        // Creating a model in memory should populate defaults that keep calculations safe.
        $inventory = new Inventory();

        $this->assertSame(0, $inventory->quantity);
        $this->assertSame(0, $inventory->reserved);
        $this->assertSame(0, $inventory->incoming);
        $this->assertSame(0, $inventory->threshold);
        $this->assertTrue($inventory->is_tracked);
    }
}
