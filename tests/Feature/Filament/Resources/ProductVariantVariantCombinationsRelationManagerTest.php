<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\EditProductVariants;
use App\Filament\Resources\ProductVariantResource\RelationManagers\VariantCombinationsRelationManager;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantVariantCombinationsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variant_edit_variant_combinations_relation_page_does_not_return_server_error(): void
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
            'name'           => 'Combination Product',
            'slug'           => 'combination-product',
            'sku'            => 'COMB-PROD-001',
            'price'          => 39.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $combination = VariantCombination::query()->create([
            'product_id'             => $product->getKey(),
            'attribute_combinations' => ['Size' => 'M', 'Color' => 'Red'],
            'is_available'           => true,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id'               => $product->getKey(),
            'sku'                      => 'COMB-VAR-001',
            'name'                     => 'Combination Variant',
            'price'                    => 29.99,
            'cost_price'               => 10.00,
            'stock_quantity'           => 5,
            'is_enabled'               => true,
            'variant_combination_hash' => $combination->combination_hash,
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/product-variants/{$variant->getRouteKey()}/edit?relation=8");

        $this->assertLessThan(500, $response->status());
    }

    public function test_variant_combinations_relation_manager_sorting_accessor_column_does_not_error(): void
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
            'name'           => 'Combination Product Sort',
            'slug'           => 'combination-product-sort',
            'sku'            => 'COMB-SORT-001',
            'price'          => 29.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $combination = VariantCombination::query()->create([
            'product_id'             => $product->getKey(),
            'attribute_combinations' => ['Size' => 'L', 'Color' => 'Blue'],
            'is_available'           => true,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id'               => $product->getKey(),
            'sku'                      => 'COMB-SORT-VAR-001',
            'name'                     => 'Combination Sort Variant',
            'price'                    => 19.99,
            'cost_price'               => 8.00,
            'stock_quantity'           => 3,
            'is_enabled'               => true,
            'variant_combination_hash' => $combination->combination_hash,
        ]);

        $this->actingAs($admin);

        Livewire::test(VariantCombinationsRelationManager::class, [
            'ownerRecord' => $variant,
            'pageClass'   => EditProductVariants::class,
        ])
            ->sortTable('formatted_combinations')
            ->assertStatus(200);
    }

    public function test_variant_combinations_relation_page_handles_legacy_malformed_json_payloads(): void
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
            'name'           => 'Combination Product Legacy',
            'slug'           => 'combination-product-legacy',
            'sku'            => 'COMB-LEG-001',
            'price'          => 19.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $variant = ProductVariant::query()->create([
            'product_id'               => $product->getKey(),
            'sku'                      => 'COMB-LEG-VAR-001',
            'name'                     => 'Legacy Combination Variant',
            'price'                    => 17.99,
            'cost_price'               => 6.00,
            'stock_quantity'           => 2,
            'is_enabled'               => true,
            'variant_combination_hash' => 'legacy-malformed-hash',
        ]);

        DB::table('variant_combinations')->insert([
            'product_id'             => $product->getKey(),
            'attribute_combinations' => 'INVALID_JSON_PAYLOAD',
            'combination_hash'       => 'legacy-malformed-hash',
            'is_available'           => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/product-variants/{$variant->getRouteKey()}/edit?relation=8");

        $this->assertLessThan(500, $response->status());
    }
}
