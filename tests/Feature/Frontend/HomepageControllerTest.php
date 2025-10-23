<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class HomepageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_live_data(): void
    {
        Currency::factory()->eur()->default()->create(['id' => 1]);

        $brand = Brand::factory()->create([
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $product = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDay(),
            ]);

        $product->categories()->attach($category->id);

        Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id' => $product->id,
            'currency_id' => 1,
            'amount' => 99.99,
            'compare_amount' => 129.99,
            'is_enabled' => true,
        ]);

        Review::factory()
            ->approved()
            ->for($product)
            ->create();

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSeeText($product->name)
            ->assertSeeText($category->name)
            ->assertSeeText($brand->name);
    }
}
