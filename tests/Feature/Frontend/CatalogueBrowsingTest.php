<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogueBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_catalogue_sections(): void
    {
        $brand = Brand::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 12,
            'price' => 120,
            'sale_price' => 96,
            'manage_stock' => true,
            'stock_quantity' => 15,
        ]);

        $product->categories()->attach($category);

        Review::factory()->approved()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('frontend.home.index');
        $response->assertViewHasAll([
            'stats',
            'featuredProducts',
            'latestProducts',
            'trendingProducts',
            'saleProducts',
            'topCategories',
            'highlightedBrands',
        ]);
        $response->assertSeeText($product->name);
    }

    public function test_product_listing_filters_featured_products(): void
    {
        $brand = Brand::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $featuredProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 8,
            'price' => 150,
            'sale_price' => 120,
            'manage_stock' => true,
            'stock_quantity' => 10,
            'name' => 'Featured Impact Drill',
        ]);
        $additionalFeaturedProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subHours(2),
            'requests_count' => 5,
            'price' => 140,
            'sale_price' => 112,
            'manage_stock' => true,
            'stock_quantity' => 8,
            'name' => 'Premium Laser Level',
        ]);

        $visibleProducts = collect([$featuredProduct, $additionalFeaturedProduct]);

        $nonFeaturedProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subHours(5),
                'name' => 'Standard Non-Featured Product',
            ]);

        $hiddenProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => false,
                'status' => 'published',
                'published_at' => now()->subHours(3),
                'name' => 'Hidden Catalogue Product',
            ]);

        $scheduledProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'status' => 'published',
                'published_at' => now()->addDay(),
                'name' => 'Scheduled Future Product',
            ]);

        $visibleProducts->each(fn (Product $product) => $product->categories()->attach($category->getKey()));
        $nonFeaturedProduct->categories()->attach($category->getKey());
        $hiddenProduct->categories()->attach($category->getKey());
        $scheduledProduct->categories()->attach($category->getKey());

        $response = $this->get(route('frontend.products.index', ['filter' => 'featured']));

        $response->assertOk();
        $response->assertViewIs('frontend.products.index');
        $response->assertViewHas('products', function ($paginator) use ($visibleProducts, $nonFeaturedProduct, $hiddenProduct, $scheduledProduct) {
            if (! $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                return false;
            }

            $ids = $paginator->pluck('id');

            return $visibleProducts->every(fn (Product $product) => $ids->contains($product->id))
                && ! $ids->contains($nonFeaturedProduct->id)
                && ! $ids->contains($hiddenProduct->id)
                && ! $ids->contains($scheduledProduct->id);
        });
        $response->assertSeeText('Discover professional tools for every job');
        foreach ($visibleProducts as $product) {
            $response->assertSeeText($product->name);
        }
    }

    public function test_category_page_displays_related_products(): void
    {
        $brand = Brand::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $categoryProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 6,
            'price' => 110,
            'sale_price' => 88,
            'manage_stock' => true,
            'stock_quantity' => 20,
            'name' => 'Category Exclusive Angle Grinder',
        ]);

        $categoryProduct->categories()->attach($category);

        $otherProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subHours(6),
            'requests_count' => 3,
            'price' => 118,
            'sale_price' => 94,
            'manage_stock' => true,
            'stock_quantity' => 14,
            'name' => 'Do Not Show Outside Category',
        ]);

        $hiddenProduct = Product::factory()->for($brand)->create([
            'is_visible' => false,
            'status' => 'published',
            'published_at' => now()->subHours(2),
        ]);
        $hiddenProduct->categories()->attach($category->getKey());

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk();
        $response->assertViewIs('frontend.categories.show');
        $response->assertViewHas('category', fn ($viewCategory) => $viewCategory->is($category));
        $response->assertViewHas('products', function ($paginator) use ($categoryProduct, $otherProduct, $hiddenProduct) {
            if (! $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                return false;
            }

            $ids = $paginator->pluck('id');

            return $ids->contains($categoryProduct->id)
                && ! $ids->contains($otherProduct->id)
                && ! $ids->contains($hiddenProduct->id);
        });
        $response->assertSeeText($categoryProduct->name);
    }

    public function test_brand_page_surfaces_products_and_categories(): void
    {
        $brand = Brand::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $brandProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 4,
            'price' => 130,
            'sale_price' => 104,
            'manage_stock' => true,
            'stock_quantity' => 12,
            'name' => 'Brand Spotlight Drill',
        ]);

        $brandProduct->categories()->attach($category);

        $otherBrand = Brand::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        $otherProduct = Product::factory()->create([
            'brand_id' => $otherBrand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subHours(5),
            'requests_count' => 2,
            'price' => 125,
            'sale_price' => 100,
            'manage_stock' => true,
            'stock_quantity' => 9,
            'name' => 'Other Brand Should Stay Hidden',
        ]);

        $hiddenProduct = Product::factory()->for($brand)->create([
            'is_visible' => false,
            'status' => 'published',
            'published_at' => now()->subHours(2),
        ]);
        $hiddenProduct->categories()->attach($category->getKey());

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk();
        $response->assertViewIs('frontend.brands.show');
        $response->assertViewHas('brand', fn ($viewBrand) => $viewBrand->is($brand));
        $response->assertViewHas('products', function ($paginator) use ($brandProduct, $otherProduct, $hiddenProduct) {
            if (! $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                return false;
            }

            $ids = $paginator->pluck('id');

            return $ids->contains($brandProduct->id)
                && ! $ids->contains($otherProduct->id)
                && ! $ids->contains($hiddenProduct->id);
        });
        $response->assertSeeText($brandProduct->name);
    }
}
