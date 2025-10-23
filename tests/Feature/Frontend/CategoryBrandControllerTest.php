<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryBrandControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_lists_categories(): void
    {
        $category = Category::factory()->create(['is_visible' => true]);

        $response = $this->get(route('frontend.categories.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.categories.index');
        $response->assertSee($category->name);
    }

    public function test_category_show_lists_products(): void
    {
        $category = Category::factory()->create(['is_visible' => true]);
        $brand = Brand::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now(),
        ]);
        $product->categories()->attach($category->id);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_brand_index_lists_brands(): void
    {
        $brand = Brand::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $response = $this->get(route('frontend.brands.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.brands.index');
        $response->assertSee($brand->name);
    }

    public function test_brand_show_lists_products(): void
    {
        $brand = Brand::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $category = Category::factory()->create(['is_visible' => true]);
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now(),
        ]);
        $product->categories()->attach($category->id);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk();
        $response->assertSee($product->name);
    }
}
