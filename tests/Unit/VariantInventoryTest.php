<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Location;
use App\Models\Partner;
use App\Models\VariantInventory;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VariantInventoryTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private ProductVariant $variant;
    private Location $location;
    private Partner $supplier;
    private VariantInventory $stockItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
        $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $this->location = Location::factory()->create();
        $this->supplier = Partner::factory()->create();
        $this->stockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
            'stock' => 100,
            'reserved' => 10,
            'threshold' => 20,
            'reorder_point' => 15,
            'cost_per_unit' => 15.50,
        ]);
    }

    public function test_can_calculate_available_stock(): void
    {
        $this->assertEquals(90, $this->stockItem->available_stock); // 100 - 10
    }

    public function test_can_calculate_stock_value(): void
    {
        $expectedValue = 100 * 15.50; // stock * cost_per_unit
        $this->assertEquals($expectedValue, $this->stockItem->stock_value);
    }

    public function test_can_calculate_reserved_value(): void
    {
        $expectedValue = 10 * 15.50; // reserved * cost_per_unit
        $this->assertEquals($expectedValue, $this->stockItem->reserved_value);
    }

    public function test_can_calculate_total_value(): void
    {
        $expectedValue = (100 + 10) * 15.50; // (stock + reserved) * cost_per_unit
        $this->assertEquals($expectedValue, $this->stockItem->total_value);
    }

    public function test_can_determine_low_stock(): void
    {
        $lowStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $this->assertTrue($lowStockItem->isLowStock());
        $this->assertFalse($this->stockItem->isLowStock());
    }

    public function test_can_determine_out_of_stock(): void
    {
        $outOfStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 0,
        ]);

        $this->assertTrue($outOfStockItem->isOutOfStock());
        $this->assertFalse($this->stockItem->isOutOfStock());
    }

    public function test_can_determine_needs_reorder(): void
    {
        $needsReorderItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'reorder_point' => 10,
        ]);

        $this->assertTrue($needsReorderItem->needsReorder());
        $this->assertFalse($this->stockItem->needsReorder());
    }

    public function test_can_determine_stock_status(): void
    {
        $this->assertEquals('in_stock', $this->stockItem->stock_status);

        $lowStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $this->assertEquals('low_stock', $lowStockItem->stock_status);

        $outOfStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 0,
        ]);

        $this->assertEquals('out_of_stock', $outOfStockItem->stock_status);

        $needsReorderItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'reorder_point' => 10,
        ]);

        $this->assertEquals('needs_reorder', $needsReorderItem->stock_status);

        $notTrackedItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'is_tracked' => false,
        ]);

        $this->assertEquals('not_tracked', $notTrackedItem->stock_status);
    }

    public function test_can_reserve_stock(): void
    {
        $initialReserved = $this->stockItem->reserved;
        $reserveQuantity = 5;

        $result = $this->stockItem->reserve($reserveQuantity);

        $this->assertTrue($result);
        $this->stockItem->refresh();
        $this->assertEquals($initialReserved + $reserveQuantity, $this->stockItem->reserved);
    }

    public function test_cannot_reserve_more_than_available(): void
    {
        $availableStock = $this->stockItem->available_stock;
        $reserveQuantity = $availableStock + 10;

        $result = $this->stockItem->reserve($reserveQuantity);

        $this->assertFalse($result);
        $this->stockItem->refresh();
        $this->assertEquals(10, $this->stockItem->reserved); // Should remain unchanged
    }

    public function test_can_unreserve_stock(): void
    {
        $initialReserved = $this->stockItem->reserved;
        $unreserveQuantity = 3;

        $this->stockItem->unreserve($unreserveQuantity);

        $this->stockItem->refresh();
        $this->assertEquals($initialReserved - $unreserveQuantity, $this->stockItem->reserved);
    }

    public function test_can_adjust_stock(): void
    {
        $initialStock = $this->stockItem->stock;
        $adjustmentQuantity = 25;

        $this->stockItem->adjustStock($adjustmentQuantity, 'manual_adjustment');

        $this->stockItem->refresh();
        $this->assertEquals($initialStock + $adjustmentQuantity, $this->stockItem->stock);

        // Check that stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'variant_inventory_id' => $this->stockItem->id,
            'quantity' => $adjustmentQuantity,
            'type' => 'in',
            'reason' => 'manual_adjustment',
        ]);
    }

    public function test_can_adjust_stock_negative(): void
    {
        $initialStock = $this->stockItem->stock;
        $adjustmentQuantity = -15;

        $this->stockItem->adjustStock($adjustmentQuantity, 'damage');

        $this->stockItem->refresh();
        $this->assertEquals($initialStock + $adjustmentQuantity, $this->stockItem->stock);

        // Check that stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'variant_inventory_id' => $this->stockItem->id,
            'quantity' => $adjustmentQuantity,
            'type' => 'out',
            'reason' => 'damage',
        ]);
    }

    public function test_can_check_if_can_reserve(): void
    {
        $this->assertTrue($this->stockItem->canReserve(50));
        $this->assertTrue($this->stockItem->canReserve(90)); // Available stock
        $this->assertFalse($this->stockItem->canReserve(91)); // More than available
        $this->assertFalse($this->stockItem->canReserve(100)); // More than available
    }

    public function test_can_check_expiring_soon(): void
    {
        $expiringSoonItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->addDays(15),
        ]);

        $this->assertTrue($expiringSoonItem->isExpiringSoon(30));
        $this->assertFalse($expiringSoonItem->isExpiringSoon(10));
        $this->assertFalse($this->stockItem->isExpiringSoon(30)); // No expiry date
    }

    public function test_can_check_expired(): void
    {
        $expiredItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->subDays(5),
        ]);

        $this->assertTrue($expiredItem->isExpired());
        $this->assertFalse($this->stockItem->isExpired()); // No expiry date
    }

    public function test_belongs_to_variant(): void
    {
        $this->assertInstanceOf(ProductVariant::class, $this->stockItem->variant);
        $this->assertEquals($this->variant->id, $this->stockItem->variant->id);
    }

    public function test_belongs_to_location(): void
    {
        $this->assertInstanceOf(Location::class, $this->stockItem->location);
        $this->assertEquals($this->location->id, $this->stockItem->location->id);
    }

    public function test_belongs_to_supplier(): void
    {
        $this->assertInstanceOf(Partner::class, $this->stockItem->supplier);
        $this->assertEquals($this->supplier->id, $this->stockItem->supplier->id);
    }

    public function test_has_many_stock_movements(): void
    {
        $movement1 = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
        ]);

        $movement2 = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
        ]);

        $this->assertCount(2, $this->stockItem->stockMovements);
        $this->assertTrue($this->stockItem->stockMovements->contains($movement1));
        $this->assertTrue($this->stockItem->stockMovements->contains($movement2));
    }

    public function test_can_scope_tracked(): void
    {
        $trackedItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'is_tracked' => true,
        ]);

        $notTrackedItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'is_tracked' => false,
        ]);

        $trackedItems = VariantInventory::tracked()->get();

        $this->assertTrue($trackedItems->contains($trackedItem));
        $this->assertTrue($trackedItems->contains($this->stockItem)); // Default is tracked
        $this->assertFalse($trackedItems->contains($notTrackedItem));
    }

    public function test_can_scope_low_stock(): void
    {
        $lowStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $lowStockItems = VariantInventory::lowStock()->get();

        $this->assertTrue($lowStockItems->contains($lowStockItem));
        $this->assertFalse($lowStockItems->contains($this->stockItem));
    }

    public function test_can_scope_out_of_stock(): void
    {
        $outOfStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 0,
        ]);

        $outOfStockItems = VariantInventory::outOfStock()->get();

        $this->assertTrue($outOfStockItems->contains($outOfStockItem));
        $this->assertFalse($outOfStockItems->contains($this->stockItem));
    }

    public function test_can_scope_needs_reorder(): void
    {
        $needsReorderItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'reorder_point' => 10,
        ]);

        $needsReorderItems = VariantInventory::needsReorder()->get();

        $this->assertTrue($needsReorderItems->contains($needsReorderItem));
        $this->assertFalse($needsReorderItems->contains($this->stockItem));
    }

    public function test_can_scope_expiring_soon(): void
    {
        $expiringSoonItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->addDays(15),
        ]);

        $expiringSoonItems = VariantInventory::expiringSoon(30)->get();

        $this->assertTrue($expiringSoonItems->contains($expiringSoonItem));
        $this->assertFalse($expiringSoonItems->contains($this->stockItem));
    }

    public function test_can_scope_by_status(): void
    {
        $activeItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'active',
        ]);

        $inactiveItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'inactive',
        ]);

        $activeItems = VariantInventory::byStatus('active')->get();

        $this->assertTrue($activeItems->contains($activeItem));
        $this->assertTrue($activeItems->contains($this->stockItem)); // Default is active
        $this->assertFalse($activeItems->contains($inactiveItem));
    }

    public function test_can_scope_by_location(): void
    {
        $anotherLocation = Location::factory()->create();
        $anotherStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $anotherLocation->id,
        ]);

        $locationItems = VariantInventory::byLocation($this->location->id)->get();

        $this->assertTrue($locationItems->contains($this->stockItem));
        $this->assertFalse($locationItems->contains($anotherStockItem));
    }

    public function test_can_scope_by_variant(): void
    {
        $anotherVariant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $anotherStockItem = VariantInventory::factory()->create([
            'variant_id' => $anotherVariant->id,
            'location_id' => $this->location->id,
        ]);

        $variantItems = VariantInventory::byVariant($this->variant->id)->get();

        $this->assertTrue($variantItems->contains($this->stockItem));
        $this->assertFalse($variantItems->contains($anotherStockItem));
    }
}
