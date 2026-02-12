<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductSimilarity;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductVariantSimilaritiesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variant_edit_similarities_relation_page_does_not_return_server_error(): void
    {
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
            'name'           => 'Main Product',
            'slug'           => 'main-product',
            'sku'            => 'SIM-MAIN-001',
            'price'          => 99.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $similarProduct = Product::query()->create([
            'name'           => 'Similar Product',
            'slug'           => 'similar-product',
            'sku'            => 'SIM-ALT-001',
            'price'          => 79.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $variant = ProductVariant::query()->create([
            'product_id'     => $product->getKey(),
            'sku'            => 'SIM-VAR-001',
            'name'           => 'Main Variant',
            'price'          => 49.99,
            'cost_price'     => 20.00,
            'stock_quantity' => 10,
            'is_enabled'     => true,
        ]);

        ProductSimilarity::query()->create([
            'product_id'         => $product->getKey(),
            'similar_product_id' => $similarProduct->getKey(),
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/product-variants/{$variant->getRouteKey()}/edit?relation=8");

        $this->assertLessThan(500, $response->status());
    }
}
