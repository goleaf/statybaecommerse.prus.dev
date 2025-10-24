<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocalizedProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_localized_product_page_renders_successfully(): void
    {
        $brand = Brand::factory()->create([
            'is_enabled' => true,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        $product->categories()->sync([$category->id]);

        $response = $this->get(route('localized.products.show', [
            'locale' => 'en',
            'product' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee($product->name);
    }
}
