<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_featured_and_latest_products(): void
    {
        $brand = Brand::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $featured = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_featured' => true,
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now()->subDay(),
        ]);
        $featured->categories()->attach($category->id);

        $latest = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now(),
        ]);
        $latest->categories()->attach($category->id);

        Discount::factory()->create([
            'status' => 'active',
            'priority' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('frontend.home.index');
        $response->assertViewHas('featuredProducts', fn ($products) => $products->contains($featured));
        $response->assertViewHas('latestProducts', fn ($products) => $products->contains($latest));
        $response->assertViewHas('popularCategories', fn ($categories) => $categories->contains($category));
        $response->assertViewHas('popularBrands', fn ($brands) => $brands->contains($brand));
    }
}
