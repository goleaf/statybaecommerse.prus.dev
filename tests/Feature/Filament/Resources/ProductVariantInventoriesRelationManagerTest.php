<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\EditProductVariants;
use App\Filament\Resources\ProductVariantResource\RelationManagers\InventoriesRelationManager;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantInventoriesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private Location $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $product = Product::query()->create([
            'name'           => 'Variant Inventory Product',
            'slug'           => 'variant-inventory-product',
            'sku'            => 'VAR-INV-PRD-001',
            'price'          => 49.99,
            'manage_stock'   => true,
            'stock_quantity' => 5,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id'     => $product->getKey(),
            'sku'            => 'VAR-INV-001',
            'name'           => 'Variant Inventory Entry',
            'price'          => 20.00,
            'cost_price'     => 10.00,
            'stock_quantity' => 3,
            'is_enabled'     => true,
        ]);

        $this->warehouse = Location::factory()->warehouse()->enabled()->create([
            'code' => 'WH-001',
            'name' => 'Main Warehouse',
        ]);

        $this->actingAs($admin);
    }

    public function test_inventories_relation_manager_creates_inventory_with_computed_available_quantity(): void
    {
        Livewire::test(InventoriesRelationManager::class, [
            'ownerRecord' => $this->variant,
            'pageClass'   => EditProductVariants::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.location_id', $this->warehouse->getKey())
            ->set('mountedActions.0.data.warehouse_code', '')
            ->set('mountedActions.0.data.stock', 12)
            ->set('mountedActions.0.data.reserved', 4)
            ->set('mountedActions.0.data.status', 'active')
            ->set('mountedActions.0.data.is_tracked', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $inventory = VariantInventory::withoutGlobalScopes()
            ->where('variant_id', $this->variant->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($inventory);
        $this->assertSame((int) $this->warehouse->getKey(), (int) $inventory->location_id);
        $this->assertSame('WH-001', $inventory->warehouse_code);
        $this->assertSame(12, (int) $inventory->stock);
        $this->assertSame(4, (int) $inventory->reserved);
        $this->assertSame(8, (int) $inventory->available);
    }

    public function test_product_variant_edit_inventory_relation_page_does_not_return_server_error(): void
    {
        VariantInventory::factory()->create([
            'variant_id'     => $this->variant->getKey(),
            'location_id'    => $this->warehouse->getKey(),
            'warehouse_code' => 'WH-001',
            'stock'          => 6,
            'reserved'       => 1,
            'available'      => 5,
            'status'         => 'active',
            'is_tracked'     => true,
        ]);

        $response = $this->get("/admin/product-variants/{$this->variant->getRouteKey()}/edit?relation=2");

        $this->assertLessThan(500, $response->status());
    }
}
