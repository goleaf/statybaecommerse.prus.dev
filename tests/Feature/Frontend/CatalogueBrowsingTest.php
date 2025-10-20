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
        ]);
        $featuredProduct->categories()->attach($category);

        $hiddenProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'is_featured' => false,
            'published_at' => now()->subDay(),
            'requests_count' => 2,
            'price' => 90,
            'sale_price' => null,
            'manage_stock' => true,
            'stock_quantity' => 5,
        ]);
        $hiddenProduct->categories()->attach($category);

        $response = $this->get(route('frontend.products.index', ['filter' => 'featured']));

        $response->assertOk();
        $response->assertViewIs('frontend.products.index');
        $response->assertViewHas('products');
        $response->assertSeeText($featuredProduct->name);
        $response->assertDontSeeText($hiddenProduct->name);
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

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 6,
            'price' => 110,
            'sale_price' => 88,
            'manage_stock' => true,
            'stock_quantity' => 20,
        ]);

        $product->categories()->attach($category);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk();
        $response->assertViewIs('frontend.categories.show');
        $response->assertViewHasAll([
            'category',
            'products',
            'relatedCategories',
            'highlightedBrands',
        ]);
        $response->assertSeeText($product->name);
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

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'requests_count' => 4,
            'price' => 130,
            'sale_price' => 104,
            'manage_stock' => true,
            'stock_quantity' => 12,
        ]);

        $product->categories()->attach($category);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk();
        $response->assertViewIs('frontend.brands.show');
        $response->assertViewHasAll([
            'brand',
            'products',
            'relatedCategories',
        ]);
        $response->assertSeeText($product->name);
        $response->assertSeeText($category->name);
    }
}
