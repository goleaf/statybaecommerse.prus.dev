<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Location;
use App\Models\Partner;
use App\Models\VariantInventory;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductVariant $variant;
    private Location $location;
    private Partner $supplier;
    private VariantInventory $stockItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
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
            'cost_per_unit' => 15.50,
        ]);
    }

    public function test_can_view_stock_index(): void
    {
        $response = $this->get(route('stock.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.stock.index');
        $response->assertViewHas('stockItems');
        $response->assertViewHas('locations');
        $response->assertViewHas('suppliers');
    }

    public function test_can_view_stock_item_details(): void
    {
        $response = $this->get(route('stock.show', $this->stockItem));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.stock.show');
        $response->assertViewHas('stock', $this->stockItem);
    }

    public function test_can_filter_stock_by_location(): void
    {
        $anotherLocation = Location::factory()->create();
        $anotherStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $anotherLocation->id,
        ]);

        $response = $this->get(route('stock.index', ['location_id' => $this->location->id]));

        $response->assertStatus(200);
        $response->assertViewHas('stockItems');
        
        $stockItems = $response->viewData('stockItems');
        $this->assertTrue($stockItems->contains('id', $this->stockItem->id));
        $this->assertFalse($stockItems->contains('id', $anotherStockItem->id));
    }

    public function test_can_filter_stock_by_supplier(): void
    {
        $anotherSupplier = Partner::factory()->create();
        $anotherStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'supplier_id' => $anotherSupplier->id,
        ]);

        $response = $this->get(route('stock.index', ['supplier_id' => $this->supplier->id]));

        $response->assertStatus(200);
        $response->assertViewHas('stockItems');
        
        $stockItems = $response->viewData('stockItems');
        $this->assertTrue($stockItems->contains('id', $this->stockItem->id));
        $this->assertFalse($stockItems->contains('id', $anotherStockItem->id));
    }

    public function test_can_filter_low_stock_items(): void
    {
        $lowStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $response = $this->get(route('stock.index', ['stock_status' => 'low_stock']));

        $response->assertStatus(200);
        $response->assertViewHas('stockItems');
        
        $stockItems = $response->viewData('stockItems');
        $this->assertTrue($stockItems->contains('id', $lowStockItem->id));
        $this->assertFalse($stockItems->contains('id', $this->stockItem->id));
    }

    public function test_can_filter_out_of_stock_items(): void
    {
        $outOfStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 0,
        ]);

        $response = $this->get(route('stock.index', ['stock_status' => 'out_of_stock']));

        $response->assertStatus(200);
        $response->assertViewHas('stockItems');
        
        $stockItems = $response->viewData('stockItems');
        $this->assertTrue($stockItems->contains('id', $outOfStockItem->id));
        $this->assertFalse($stockItems->contains('id', $this->stockItem->id));
    }

    public function test_can_search_stock_by_product_name(): void
    {
        $anotherProduct = Product::factory()->create(['name' => 'Another Product']);
        $anotherVariant = ProductVariant::factory()->create(['product_id' => $anotherProduct->id]);
        $anotherStockItem = VariantInventory::factory()->create([
            'variant_id' => $anotherVariant->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->get(route('stock.index', ['search' => $this->product->name]));

        $response->assertStatus(200);
        $response->assertViewHas('stockItems');
        
        $stockItems = $response->viewData('stockItems');
        $this->assertTrue($stockItems->contains('id', $this->stockItem->id));
        $this->assertFalse($stockItems->contains('id', $anotherStockItem->id));
    }

    public function test_can_adjust_stock(): void
    {
        $initialStock = $this->stockItem->stock;
        $adjustmentQuantity = 25;

        $response = $this->post(route('stock.adjust', $this->stockItem), [
            'quantity' => $adjustmentQuantity,
            'reason' => 'manual_adjustment',
            'notes' => 'Test adjustment',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

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

    public function test_can_reserve_stock(): void
    {
        $initialReserved = $this->stockItem->reserved;
        $reserveQuantity = 5;

        $response = $this->post(route('stock.reserve', $this->stockItem), [
            'quantity' => $reserveQuantity,
            'notes' => 'Test reservation',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->stockItem->refresh();
        $this->assertEquals($initialReserved + $reserveQuantity, $this->stockItem->reserved);
    }

    public function test_cannot_reserve_more_than_available(): void
    {
        $availableStock = $this->stockItem->available_stock;
        $reserveQuantity = $availableStock + 10;

        $response = $this->post(route('stock.reserve', $this->stockItem), [
            'quantity' => $reserveQuantity,
            'notes' => 'Test reservation',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_can_unreserve_stock(): void
    {
        $initialReserved = $this->stockItem->reserved;
        $unreserveQuantity = 3;

        $response = $this->post(route('stock.unreserve', $this->stockItem), [
            'quantity' => $unreserveQuantity,
            'notes' => 'Test unreservation',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->stockItem->refresh();
        $this->assertEquals($initialReserved - $unreserveQuantity, $this->stockItem->reserved);
    }

    public function test_can_get_stock_movements(): void
    {
        // Create some stock movements
        StockMovement::factory()->count(3)->create([
            'variant_inventory_id' => $this->stockItem->id,
        ]);

        $response = $this->get(route('stock.movements', $this->stockItem));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'quantity',
                    'type',
                    'reason',
                    'moved_at',
                ]
            ]
        ]);
    }

    public function test_can_export_stock(): void
    {
        $response = $this->get(route('stock.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="stock_export_' . now()->format('Y-m-d_H-i-s') . '.csv"');
    }

    public function test_can_view_stock_report(): void
    {
        $response = $this->get(route('stock.report'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.stock.report');
        $response->assertViewHas('stockItems');
        $response->assertViewHas('summary');
        $response->assertViewHas('byLocation');
        $response->assertViewHas('bySupplier');
    }

    public function test_stock_item_calculates_available_stock_correctly(): void
    {
        $this->assertEquals(90, $this->stockItem->available_stock); // 100 - 10
    }

    public function test_stock_item_calculates_stock_value_correctly(): void
    {
        $expectedValue = $this->stockItem->stock * $this->stockItem->cost_per_unit;
        $this->assertEquals($expectedValue, $this->stockItem->stock_value);
    }

    public function test_stock_item_identifies_low_stock(): void
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

    public function test_stock_item_identifies_out_of_stock(): void
    {
        $outOfStockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 0,
        ]);

        $this->assertTrue($outOfStockItem->isOutOfStock());
        $this->assertFalse($this->stockItem->isOutOfStock());
    }

    public function test_stock_item_identifies_needs_reorder(): void
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

    public function test_stock_item_status_is_correct(): void
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
    }
}
