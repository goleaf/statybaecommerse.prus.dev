<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\EditProductVariants;
use App\Filament\Resources\ProductVariantResource\RelationManagers\PricesRelationManager;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantPricesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private Currency $currency;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $this->currency = Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $product = Product::query()->create([
            'name'           => 'Variant Price Product',
            'slug'           => 'variant-price-product',
            'sku'            => 'VAR-PRICE-PRD-001',
            'price'          => 99.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id'     => $product->getKey(),
            'sku'            => 'VAR-PRICE-001',
            'name'           => 'Variant Price Entry',
            'price'          => 49.99,
            'cost_price'     => 20.00,
            'stock_quantity' => 10,
            'is_enabled'     => true,
        ]);

        $this->actingAs($admin);
    }

    public function test_prices_relation_manager_creates_variant_price_with_valid_default_type(): void
    {
        Livewire::test(PricesRelationManager::class, [
            'ownerRecord' => $this->variant,
            'pageClass'   => EditProductVariants::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.currency_id', $this->currency->getKey())
            ->set('mountedActions.0.data.amount', 123.0000)
            ->set('mountedActions.0.data.is_enabled', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $price = Price::query()
            ->where('priceable_id', $this->variant->getKey())
            ->where('priceable_type', ProductVariant::class)
            ->latest('id')
            ->first();

        $this->assertNotNull($price);
        $this->assertSame('retail', $price->type);
    }

    public function test_price_model_normalizes_invalid_type_to_retail(): void
    {
        $price = Price::query()->create([
            'priceable_id'   => $this->variant->getKey(),
            'priceable_type' => ProductVariant::class,
            'currency_id'    => $this->currency->getKey(),
            'amount'         => 111.1111,
            'type'           => '123',
            'is_enabled'     => true,
        ]);

        $this->assertSame('retail', $price->fresh()->type);
    }

    public function test_product_variant_edit_prices_relation_page_does_not_return_server_error(): void
    {
        $response = $this->get("/admin/product-variants/{$this->variant->getRouteKey()}/edit?relation=1");

        $this->assertLessThan(500, $response->status());
    }
}
