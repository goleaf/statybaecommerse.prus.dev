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

final class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductVariant $variant;
    private Location $location;
    private Partner $supplier;
    private VariantInventory $stockItem;
    private StockMovement $stockMovement;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required countries first
        \App\Models\Country::factory()->create(['cca2' => 'LT', 'name' => 'Lithuania']);
        \App\Models\Country::factory()->create(['cca2' => 'US', 'name' => 'United States']);
        \App\Models\Country::factory()->create(['cca2' => 'GB', 'name' => 'United Kingdom']);

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $this->location = Location::factory()->create();
        $this->supplier = Partner::factory()->create();
        $this->stockItem = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
        ]);
        $this->stockMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_belongs_to_variant_inventory(): void
    {
        $this->assertInstanceOf(VariantInventory::class, $this->stockMovement->variantInventory);
        $this->assertEquals($this->stockItem->id, $this->stockMovement->variantInventory->id);
    }

    public function test_belongs_to_user(): void
    {
        $this->assertInstanceOf(User::class, $this->stockMovement->user);
        $this->assertEquals($this->user->id, $this->stockMovement->user->id);
    }

    public function test_can_scope_inbound(): void
    {
        $inboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'in',
        ]);

        $outboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'out',
        ]);

        $inboundMovements = StockMovement::inbound()->get();

        $this->assertTrue($inboundMovements->contains($inboundMovement));
        $this->assertFalse($inboundMovements->contains($outboundMovement));
    }

    public function test_can_scope_outbound(): void
    {
        $inboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'in',
        ]);

        $outboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'out',
        ]);

        $outboundMovements = StockMovement::outbound()->get();

        $this->assertTrue($outboundMovements->contains($outboundMovement));
        $this->assertFalse($outboundMovements->contains($inboundMovement));
    }

    public function test_can_scope_by_reason(): void
    {
        $saleMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'sale',
        ]);

        $returnMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'return',
        ]);

        $saleMovements = StockMovement::byReason('sale')->get();

        $this->assertTrue($saleMovements->contains($saleMovement));
        $this->assertFalse($saleMovements->contains($returnMovement));
    }

    public function test_can_scope_by_user(): void
    {
        $anotherUser = User::factory()->create();
        $anotherMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'user_id' => $anotherUser->id,
        ]);

        $userMovements = StockMovement::byUser($this->user->id)->get();

        $this->assertTrue($userMovements->contains($this->stockMovement));
        $this->assertFalse($userMovements->contains($anotherMovement));
    }

    public function test_can_scope_recent(): void
    {
        $recentMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'moved_at' => now()->subDays(5),
        ]);

        $oldMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'moved_at' => now()->subDays(35),
        ]);

        $recentMovements = StockMovement::recent(30)->get();

        $this->assertTrue($recentMovements->contains($recentMovement));
        $this->assertTrue($recentMovements->contains($this->stockMovement));
        $this->assertFalse($recentMovements->contains($oldMovement));
    }

    public function test_can_get_type_label(): void
    {
        $inboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'in',
        ]);

        $outboundMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'out',
        ]);

        $this->assertEquals(__('inventory.stock_in'), $inboundMovement->type_label);
        $this->assertEquals(__('inventory.stock_out'), $outboundMovement->type_label);
    }

    public function test_can_get_reason_label(): void
    {
        $saleMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'sale',
        ]);

        $returnMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'return',
        ]);

        $adjustmentMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'adjustment',
        ]);

        $manualAdjustmentMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'manual_adjustment',
        ]);

        $restockMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'restock',
        ]);

        $damageMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'damage',
        ]);

        $theftMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'theft',
        ]);

        $transferMovement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'transfer',
        ]);

        $this->assertEquals(__('inventory.reason_sale'), $saleMovement->reason_label);
        $this->assertEquals(__('inventory.reason_return'), $returnMovement->reason_label);
        $this->assertEquals(__('inventory.reason_adjustment'), $adjustmentMovement->reason_label);
        $this->assertEquals(__('inventory.reason_manual_adjustment'), $manualAdjustmentMovement->reason_label);
        $this->assertEquals(__('inventory.reason_restock'), $restockMovement->reason_label);
        $this->assertEquals(__('inventory.reason_damage'), $damageMovement->reason_label);
        $this->assertEquals(__('inventory.reason_theft'), $theftMovement->reason_label);
        $this->assertEquals(__('inventory.reason_transfer'), $transferMovement->reason_label);
    }

    public function test_returns_unknown_for_invalid_type(): void
    {
        // Create a movement with valid type first, then modify it in memory to test the logic
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'type' => 'in',
        ]);
        
        // Test the logic by setting an invalid type in memory (not saving to DB)
        $movement->type = 'invalid';
        $this->assertEquals(__('inventory.unknown'), $movement->type_label);
    }

    public function test_returns_reason_for_unknown_reason(): void
    {
        // Create a movement with valid reason first, then modify it in memory to test the logic
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reason' => 'sale',
        ]);
        
        // Test the logic by setting an unknown reason in memory (not saving to DB)
        $movement->reason = 'unknown_reason';
        $this->assertEquals('unknown_reason', $movement->reason_label);
    }

    public function test_can_create_stock_movement_with_all_fields(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'quantity' => 25,
            'type' => 'in',
            'reason' => 'restock',
            'reference' => 'PO-12345',
            'notes' => 'Restocked from supplier',
            'user_id' => $this->user->id,
            'moved_at' => now(),
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'variant_inventory_id' => $this->stockItem->id,
            'quantity' => 25,
            'type' => 'in',
            'reason' => 'restock',
            'reference' => 'PO-12345',
            'notes' => 'Restocked from supplier',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_create_stock_movement_without_user(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'user_id' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'variant_inventory_id' => $this->stockItem->id,
            'user_id' => null,
        ]);

        $this->assertNull($movement->user);
    }

    public function test_can_create_stock_movement_without_reference(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'reference' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'variant_inventory_id' => $this->stockItem->id,
            'reference' => null,
        ]);
    }

    public function test_can_create_stock_movement_without_notes(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'notes' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'variant_inventory_id' => $this->stockItem->id,
            'notes' => null,
        ]);
    }

    public function test_quantity_is_casted_to_integer(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'quantity' => '25',
        ]);

        $this->assertIsInt($movement->quantity);
        $this->assertEquals(25, $movement->quantity);
    }

    public function test_moved_at_is_casted_to_datetime(): void
    {
        $movement = StockMovement::factory()->create([
            'variant_inventory_id' => $this->stockItem->id,
            'moved_at' => '2024-01-20 12:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $movement->moved_at);
    }

    public function test_can_get_fillable_attributes(): void
    {
        $fillable = [
            'variant_inventory_id',
            'quantity',
            'type',
            'reason',
            'reference',
            'notes',
            'user_id',
            'moved_at',
        ];

        $this->assertEquals($fillable, $this->stockMovement->getFillable());
    }

    public function test_can_get_table_name(): void
    {
        $this->assertEquals('stock_movements', $this->stockMovement->getTable());
    }
}

