<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\TestCase;

final class CatalogueBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedProduct(array $overrides = []): Product
    {
        $brand = $overrides['brand'] ?? Brand::factory()->create();
        unset($overrides['brand']);

        return Product::factory()
            ->for($brand)
            ->create(array_merge([
                'is_visible' => true,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'is_featured' => false,
            ], $overrides));
    }

    public function test_homepage_catalogue_sections_render(): void
    {
        Cache::flush();

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = $this->createPublishedProduct([
            'is_featured' => true,
            'brand' => $brand,
        ]);
        $product->categories()->attach($category->getKey());

        Review::factory()->for($product)->approved()->create(['rating' => 5]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('frontend.home.index');
        $response->assertViewHasAll([
            'stats',
            'featuredProducts',
            'latestProducts',
            'popularCategories',
            'topBrands',
        ]);
        $response->assertSeeText($product->name);
        $response->assertSeeText($category->name);
        $response->assertSeeText($brand->name);
    }

    public function test_product_listing_displays_catalogue_data(): void
    {
        Cache::flush();

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $visibleProducts = Product::factory()
            ->count(3)
            ->for($brand)
            ->create([
                'is_visible' => true,
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'is_featured' => true,
            ]);

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

        $response = $this->get(route('frontend.products.index', ['filter' => 'featured', 'sort' => 'latest']));

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

    public function test_category_page_lists_associated_products(): void
    {
        Cache::flush();

        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $brand = Brand::factory()->create();

        $categoryProduct = $this->createPublishedProduct([
            'brand' => $brand,
            'is_featured' => true,
            'published_at' => now()->subHour(),
        ]);
        $categoryProduct->categories()->attach($category->getKey());

        $otherProduct = $this->createPublishedProduct([
            'brand' => $brand,
            'published_at' => now()->subHours(5),
        ]);
        $otherProduct->categories()->attach($otherCategory->getKey());

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

    public function test_brand_page_lists_associated_products(): void
    {
        Cache::flush();

        $brand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();
        $category = Category::factory()->create();

        $brandProduct = $this->createPublishedProduct([
            'brand' => $brand,
            'published_at' => now()->subHour(),
            'is_featured' => true,
        ]);
        $brandProduct->categories()->attach($category->getKey());

        $otherProduct = $this->createPublishedProduct([
            'brand' => $otherBrand,
            'published_at' => now()->subHours(6),
        ]);
        $otherProduct->categories()->attach($category->getKey());

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
