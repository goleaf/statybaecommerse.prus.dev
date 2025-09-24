<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Location;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VariantStockResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    public function test_can_list_variant_stocks(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $variantStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 100,
            'threshold' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->assertCanSeeTableRecords([$variantStock])
            ->assertCanRenderTableColumn('variant.product.name')
            ->assertCanRenderTableColumn('location.name')
            ->assertCanRenderTableColumn('stock')
            ->assertCanRenderTableColumn('available_stock');
    }

    public function test_can_create_variant_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();
        $supplier = Partner::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\CreateVariantStock::class)
            ->fillForm([
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'stock' => 50,
                'reserved' => 5,
                'incoming' => 10,
                'threshold' => 5,
                'reorder_point' => 3,
                'max_stock_level' => 200,
                'cost_per_unit' => 25.50,
                'supplier_id' => $supplier->id,
                'batch_number' => 'BATCH001',
                'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'active',
                'is_tracked' => true,
                'notes' => 'Test stock entry',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 50,
            'reserved' => 5,
            'incoming' => 10,
            'threshold' => 5,
            'reorder_point' => 3,
            'max_stock_level' => 200,
            'cost_per_unit' => 25.50,
            'supplier_id' => $supplier->id,
            'batch_number' => 'BATCH001',
            'status' => 'active',
            'is_tracked' => true,
            'notes' => 'Test stock entry',
        ]);
    }

    public function test_can_edit_variant_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $variantStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 100,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\EditVariantStock::class, [
            'record' => $variantStock->id,
        ])
            ->fillForm([
                'stock' => 150,
                'threshold' => 20,
                'notes' => 'Updated stock entry',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $variantStock->id,
            'stock' => 150,
            'threshold' => 20,
            'notes' => 'Updated stock entry',
        ]);
    }

    public function test_can_delete_variant_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $variantStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\EditVariantStock::class, [
            'record' => $variantStock->id,
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('variant_inventories', [
            'id' => $variantStock->id,
        ]);
    }

    public function test_can_filter_by_location(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location1 = Location::factory()->create(['name' => 'Warehouse A']);
        $location2 = Location::factory()->create(['name' => 'Warehouse B']);

        $stock1 = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location1->id,
        ]);

        $stock2 = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location2->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->filterTable('location_id', $location1->id)
            ->assertCanSeeTableRecords([$stock1])
            ->assertCanNotSeeTableRecords([$stock2]);
    }

    public function test_can_filter_by_stock_status(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $lowStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $normalStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 50,
            'threshold' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->filterTable('low_stock')
            ->assertCanSeeTableRecords([$lowStock])
            ->assertCanNotSeeTableRecords([$normalStock]);
    }

    public function test_can_bulk_adjust_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $stock1 = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 100,
        ]);

        $stock2 = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 200,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->callTableBulkAction('bulk_adjust_stock', [$stock1, $stock2], [
                'quantity' => 50,
                'reason' => 'bulk_restock',
            ])
            ->assertHasNoBulkActionErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $stock1->id,
            'stock' => 150,
        ]);

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $stock2->id,
            'stock' => 250,
        ]);
    }

    public function test_can_reserve_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $variantStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 100,
            'reserved' => 0,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->callTableAction('reserve_stock', $variantStock, [
                'quantity' => 25,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $variantStock->id,
            'reserved' => 25,
        ]);
    }

    public function test_can_unreserve_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        $variantStock = VariantInventory::factory()->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 100,
            'reserved' => 50,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks::class)
            ->callTableAction('unreserve_stock', $variantStock, [
                'quantity' => 20,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $variantStock->id,
            'reserved' => 30,
        ]);
    }

    public function test_navigation_badge_shows_low_stock_count(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        // Create low stock items
        VariantInventory::factory()->count(3)->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 5,
            'threshold' => 10,
        ]);

        $this->actingAs($this->adminUser);

        $badge = \App\Filament\Resources\VariantStockResource::getNavigationBadge();

        $this->assertEquals('3', $badge);
    }

    public function test_navigation_badge_color_changes_based_on_stock_status(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $location = Location::factory()->create();

        // Create out of stock items
        VariantInventory::factory()->count(2)->create([
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'stock' => 0,
            'threshold' => 10,
        ]);

        $this->actingAs($this->adminUser);

        $badgeColor = \App\Filament\Resources\VariantStockResource::getNavigationBadgeColor();

        $this->assertEquals('danger', $badgeColor);
    }
}
