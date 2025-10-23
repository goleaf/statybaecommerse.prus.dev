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

        $hiddenProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => false,
                'status' => 'published',
                'published_at' => now()->subHours(3),
            ]);

        $draftProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'status' => 'draft',
                'published_at' => now()->subHours(4),
            ]);

        $scheduledProduct = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'status' => 'published',
                'published_at' => now()->addDay(),
            ]);

        $visibleProducts->each(fn (Product $product) => $product->categories()->attach($category->getKey()));
        $hiddenProduct->categories()->attach($category->getKey());
        $draftProduct->categories()->attach($category->getKey());
        $scheduledProduct->categories()->attach($category->getKey());

        $response = $this->get(route('frontend.products.index', ['filter' => 'featured']));

        $response->assertOk();
        $response->assertViewIs('frontend.products.index');
        $response->assertViewHas('products');
        $response->assertSeeText('Discover construction essentials');
        foreach ($visibleProducts as $product) {
            $response->assertSeeText($product->name);
        }
        $response->assertDontSeeText($hiddenProduct->name);
        $response->assertDontSeeText($draftProduct->name);
        $response->assertDontSeeText($scheduledProduct->name);
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
        $response->assertSeeText($categoryProduct->name);
        $response->assertDontSeeText($otherProduct->name);
        $response->assertDontSeeText($hiddenProduct->name);
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
        $response->assertSeeText($brandProduct->name);
        $response->assertDontSeeText($otherProduct->name);
        $response->assertDontSeeText($hiddenProduct->name);
    }
}
