<?php declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\ProductCatalog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_product_catalog(): void
    {
        Livewire::test(ProductCatalog::class)
            ->assertOk();
    }

    public function test_can_display_products(): void
    {
        $products = Product::factory()->count(5)->create(['is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);

        Livewire::test(ProductCatalog::class)
            ->assertViewHas('products', function ($paginatedProducts) use ($products, $hiddenProduct) {
                $productIds = $paginatedProducts->pluck('id')->toArray();

                foreach ($products as $product) {
                    if (!in_array($product->id, $productIds)) {
                        return false;
                    }
                }

                return !in_array($hiddenProduct->id, $productIds);
            });
    }

    public function test_can_search_products(): void
    {
        $matchingProduct = Product::factory()->create([
            'name' => 'iPhone 15',
            'is_visible' => true,
        ]);

        $nonMatchingProduct = Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'is_visible' => true,
        ]);

        Livewire::test(ProductCatalog::class)
            ->set('search', 'iPhone')
            ->assertViewHas('products', function ($paginatedProducts) use ($matchingProduct, $nonMatchingProduct) {
                $productIds = $paginatedProducts->pluck('id')->toArray();
                return in_array($matchingProduct->id, $productIds) &&
                    !in_array($nonMatchingProduct->id, $productIds);
            });
    }

    public function test_can_filter_by_category(): void
    {
        $category = Category::factory()->create();
        $productInCategory = Product::factory()->create(['is_visible' => true]);
        $productNotInCategory = Product::factory()->create(['is_visible' => true]);

        $productInCategory->categories()->attach($category);

        Livewire::test(ProductCatalog::class)
            ->set('selectedCategories', [$category->id])
            ->assertViewHas('products', function ($paginatedProducts) use ($productInCategory, $productNotInCategory) {
                $productIds = $paginatedProducts->pluck('id')->toArray();
                return in_array($productInCategory->id, $productIds) &&
                    !in_array($productNotInCategory->id, $productIds);
            });
    }

    public function test_can_filter_by_brand(): void
    {
        $brand = Brand::factory()->create();
        $productWithBrand = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);
        $productWithoutBrand = Product::factory()->create([
            'brand_id' => null,
            'is_visible' => true,
        ]);

        Livewire::test(ProductCatalog::class)
            ->set('selectedBrands', [$brand->id])
            ->assertViewHas('products', function ($paginatedProducts) use ($productWithBrand, $productWithoutBrand) {
                $productIds = $paginatedProducts->pluck('id')->toArray();
                return in_array($productWithBrand->id, $productIds) &&
                    !in_array($productWithoutBrand->id, $productIds);
            });
    }

    public function test_can_filter_by_price_range(): void
    {
        $cheapProduct = Product::factory()->create([
            'price' => 50.0,
            'is_visible' => true,
        ]);
        $expensiveProduct = Product::factory()->create([
            'price' => 500.0,
            'is_visible' => true,
        ]);

        Livewire::test(ProductCatalog::class)
            ->set('priceMin', 100)
            ->set('priceMax', 1000)
            ->assertViewHas('products', function ($paginatedProducts) use ($cheapProduct, $expensiveProduct) {
                $productIds = $paginatedProducts->pluck('id')->toArray();
                return !in_array($cheapProduct->id, $productIds) &&
                    in_array($expensiveProduct->id, $productIds);
            });
    }

    public function test_can_filter_by_availability(): void
    {
        $inStockProduct = Product::factory()->create([
            'stock_quantity' => 10,
            'is_visible' => true,
        ]);
        $outOfStockProduct = Product::factory()->create([
            'stock_quantity' => 0,
            'is_visible' => true,
        ]);

        Livewire::test(ProductCatalog::class)
            ->set('availability', 'in_stock')
            ->assertViewHas('products', function ($paginatedProducts) use ($inStockProduct, $outOfStockProduct) {
                $productIds = $paginatedProducts->pluck('id')->toArray();
                return in_array($inStockProduct->id, $productIds) &&
                    !in_array($outOfStockProduct->id, $productIds);
            });
    }

    public function test_can_sort_products(): void
    {
        $productA = Product::factory()->create([
            'name' => 'Apple iPhone',
            'price' => 1000.0,
            'is_visible' => true,
        ]);
        $productB = Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'price' => 800.0,
            'is_visible' => true,
        ]);

        // Test sorting by name ascending
        Livewire::test(ProductCatalog::class)
            ->set('sortBy', 'name')
            ->set('sortDirection', 'asc')
            ->assertViewHas('products', function ($paginatedProducts) use ($productA, $productB) {
                $products = $paginatedProducts->items();
                return $products[0]->id === $productA->id && $products[1]->id === $productB->id;
            });

        // Test sorting by price descending
        Livewire::test(ProductCatalog::class)
            ->set('sortBy', 'price')
            ->set('sortDirection', 'desc')
            ->assertViewHas('products', function ($paginatedProducts) use ($productA, $productB) {
                $products = $paginatedProducts->items();
                return $products[0]->id === $productA->id && $products[1]->id === $productB->id;
            });
    }

    public function test_can_clear_filters(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Livewire::test(ProductCatalog::class)
            ->set('search', 'test')
            ->set('selectedCategories', [$category->id])
            ->set('selectedBrands', [$brand->id])
            ->set('priceMin', 100)
            ->set('priceMax', 500)
            ->set('availability', 'in_stock')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('selectedCategories', [])
            ->assertSet('selectedBrands', [])
            ->assertSet('priceMin', 0)
            ->assertSet('availability', 'all');
    }

    public function test_can_toggle_filters_visibility(): void
    {
        Livewire::test(ProductCatalog::class)
            ->assertSet('showFilters', false)
            ->call('toggleFilters')
            ->assertSet('showFilters', true)
            ->call('toggleFilters')
            ->assertSet('showFilters', false);
    }

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'is_visible' => true,
        ]);

        Livewire::test(ProductCatalog::class)
            ->call('addToCart', $product->id)
            ->assertDispatched('cart-updated')
            ->assertDispatched('notify');

        $cart = session('cart', []);
        expect($cart)->toHaveKey($product->id);
        expect($cart[$product->id]['quantity'])->toBe(1);
    }

    public function test_pagination_works(): void
    {
        Product::factory()->count(25)->create(['is_visible' => true]);

        Livewire::test(ProductCatalog::class)
            ->set('perPage', 12)
            ->assertViewHas('products', function ($paginatedProducts) {
                return $paginatedProducts->count() === 12 && $paginatedProducts->hasMorePages();
            });
    }

    public function test_can_change_per_page_setting(): void
    {
        Product::factory()->count(30)->create(['is_visible' => true]);

        Livewire::test(ProductCatalog::class)
            ->set('perPage', 24)
            ->assertViewHas('products', function ($paginatedProducts) {
                return $paginatedProducts->perPage() === 24;
            });
    }
}

