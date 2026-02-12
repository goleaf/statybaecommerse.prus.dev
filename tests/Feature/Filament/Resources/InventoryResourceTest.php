<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages\CreateInventory;
use App\Filament\Resources\InventoryResource\Schemas\InventoryForm;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

final class InventoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);
    }

    public function test_create_inventory_page_saves_inventory_for_legacy_unpublished_product(): void
    {
        $product = Product::query()->create([
            'name'           => 'Legacy Draft Product',
            'slug'           => 'legacy-draft-product',
            'sku'            => 'INV-LEGACY-001',
            'price'          => 9.99,
            'manage_stock'   => true,
            'stock_quantity' => 0,
            'status'         => 'draft',
            'is_enabled'     => false,
            'published_at'   => null,
        ]);

        $warehouse = Location::factory()->warehouse()->enabled()->create([
            'name' => 'Central Warehouse',
            'code' => 'WH-INV-001',
        ]);

        Livewire::test(CreateInventory::class)
            ->fillForm([
                'product_id'   => $product->getKey(),
                'warehouse_id' => $warehouse->getKey(),
                'qty'          => 12,
                'reserved'     => 3,
                'threshold'    => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $inventory = Inventory::query()->first();

        $this->assertNotNull($inventory);
        $this->assertSame((int) $product->getKey(), (int) $inventory->product_id);
        $this->assertSame((int) $warehouse->getKey(), (int) $inventory->location_id);
        $this->assertSame(12, (int) $inventory->quantity);
        $this->assertSame(3, (int) $inventory->reserved);
        $this->assertSame(5, (int) $inventory->threshold);
    }

    public function test_inventory_form_product_search_includes_draft_products(): void
    {
        $product = Product::query()->create([
            'name'           => 'Searchable Draft Product',
            'slug'           => 'searchable-draft-product',
            'sku'            => 'INV-SEARCH-001',
            'price'          => 15.00,
            'manage_stock'   => true,
            'stock_quantity' => 0,
            'status'         => 'draft',
            'is_enabled'     => false,
            'published_at'   => null,
        ]);

        $method = new ReflectionMethod(InventoryForm::class, 'searchProducts');
        $method->setAccessible(true);

        /** @var array<string, string> $results */
        $results = $method->invoke(null, 'INV-SEARCH-001');

        $this->assertArrayHasKey((string) $product->getKey(), $results);
        $this->assertStringContainsString('INV-SEARCH-001', $results[(string) $product->getKey()]);
    }

    public function test_inventory_form_warehouse_label_falls_back_to_code_when_name_is_empty(): void
    {
        $warehouse = Location::factory()->warehouse()->enabled()->create([
            'name' => '',
            'code' => 'WH-FALLBACK-001',
        ]);

        $method = new ReflectionMethod(InventoryForm::class, 'resolveWarehouseLabel');
        $method->setAccessible(true);

        $label = $method->invoke(null, $warehouse);

        $this->assertSame('WH-FALLBACK-001', $label);
    }
}
