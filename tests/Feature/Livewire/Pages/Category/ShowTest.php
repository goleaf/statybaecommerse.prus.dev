<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Category;

use App\Livewire\Pages\Category\Show;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_with_rating_sort_without_missing_column_errors(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $productA = Product::factory()->published()->create([
            'is_enabled'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);
        $productB = Product::factory()->published()->create([
            'is_enabled'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $category->products()->attach([$productA->getKey(), $productB->getKey()]);

        if (
            Schema::hasTable('reviews')
            && Schema::hasColumn('reviews', 'product_id')
            && Schema::hasColumn('reviews', 'rating')
        ) {
            DB::table('reviews')->insert([
                [
                    'product_id'           => $productA->getKey(),
                    'user_id'              => null,
                    'reviewer_name'        => 'Demo Reviewer A',
                    'reviewer_email'       => 'info@egisstatyba.lt',
                    'rating'               => 5,
                    'title'                => 'Great',
                    'content'              => 'Great product',
                    'is_approved'          => true,
                    'locale'               => 'lt',
                    'is_verified_purchase' => false,
                    'helpful_count'        => 0,
                    'reported_count'       => 0,
                    'is_featured'          => false,
                    'metadata'             => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'product_id'           => $productB->getKey(),
                    'user_id'              => null,
                    'reviewer_name'        => 'Demo Reviewer B',
                    'reviewer_email'       => 'info@egisstatyba.lt',
                    'rating'               => 2,
                    'title'                => 'Okay',
                    'content'              => 'Average product',
                    'is_approved'          => true,
                    'locale'               => 'lt',
                    'is_verified_purchase' => false,
                    'helpful_count'        => 0,
                    'reported_count'       => 0,
                    'is_featured'          => false,
                    'metadata'             => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
            ]);
        }

        Livewire::test(Show::class, ['category' => $category])
            ->set('sortBy', 'rating')
            ->set('sortDirection', 'desc')
            ->assertStatus(200);
    }

    public function test_it_adds_products_to_cart_from_category_page(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $product = Product::factory()->published()->create([
            'is_enabled'       => true,
            'status'           => 'published',
            'published_at'     => now()->subDay(),
            'stock_quantity'   => 10,
            'hide_add_to_cart' => false,
            'is_requestable'   => false,
        ]);

        $category->products()->attach($product->getKey());

        Livewire::test(Show::class, ['category' => $category])
            ->call('addToCart', (int) $product->getKey(), 1, null, null)
            ->assertStatus(200);

        $cart = session()->get('cart', []);
        $cartKey = (string) $product->getKey();

        $this->assertArrayHasKey($cartKey, $cart);
        $this->assertSame($product->getKey(), $cart[$cartKey]['product_id']);
        $this->assertSame(1, $cart[$cartKey]['quantity']);
    }

    public function test_it_keeps_add_to_cart_enabled_for_non_stock_managed_products(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $product = Product::factory()->published()->create([
            'is_enabled'       => true,
            'status'           => 'published',
            'published_at'     => now()->subDay(),
            'manage_stock'     => false,
            'stock_quantity'   => 0,
            'hide_add_to_cart' => false,
            'is_requestable'   => false,
        ]);

        $category->products()->attach($product->getKey());

        $component = Livewire::test(Show::class, ['category' => $category])->assertStatus(200);
        $html = $component->html();
        $productIdPattern = preg_quote((string) $product->getKey(), '/');

        $this->assertMatchesRegularExpression('/wire:click="addToCart\(' . $productIdPattern . '\)"[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/wire:click="addToCart\(' . $productIdPattern . '\)"[^>]*\sdisabled(\s|>)/', $html);

        $component->call('addToCart', (int) $product->getKey(), 1, null, null);

        $cart = session()->get('cart', []);
        $cartKey = (string) $product->getKey();

        $this->assertArrayHasKey($cartKey, $cart);
        $this->assertSame(1, $cart[$cartKey]['quantity']);
    }

    public function test_in_stock_filter_keeps_products_with_in_stock_variants(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $variantBackedProduct = Product::factory()->published()->create([
            'is_enabled'     => true,
            'status'         => 'published',
            'published_at'   => now()->subDay(),
            'manage_stock'   => true,
            'stock_quantity' => 0,
        ]);
        $category->products()->attach($variantBackedProduct->getKey());

        ProductVariant::factory()->create([
            'product_id'      => $variantBackedProduct->getKey(),
            'stock_quantity'  => 4,
            'track_inventory' => true,
        ]);

        $outOfStockProduct = Product::factory()->published()->create([
            'is_enabled'     => true,
            'status'         => 'published',
            'published_at'   => now()->subDay(),
            'manage_stock'   => true,
            'stock_quantity' => 0,
        ]);
        $category->products()->attach($outOfStockProduct->getKey());

        $component = Livewire::test(Show::class, ['category' => $category])
            ->set('inStock', true);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Data\Storefront\Home\ProductListItemData> $products */
        $products = $component->instance()->products;
        $productIds = $products->getCollection()
            ->map(static fn ($product): int => (int) ($product->id ?? 0))
            ->all();

        $this->assertContains($variantBackedProduct->getKey(), $productIds);
        $this->assertNotContains($outOfStockProduct->getKey(), $productIds);
    }
}
