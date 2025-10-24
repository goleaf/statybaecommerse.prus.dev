<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class ProductBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private function createCurrency(): void
    {
        Currency::factory()->eur()->default()->create(['id' => 1]);
    }

    public function test_product_listing_allows_search_and_filters(): void
    {
        $this->createCurrency();

        $brandA = Brand::factory()->create(['is_visible' => true, 'is_enabled' => true, 'name' => 'Makita Pro']);
        $brandB = Brand::factory()->create(['is_visible' => true, 'is_enabled' => true, 'name' => 'Bosch Baltic']);

        $categoryA = Category::factory()->create(['name' => 'Elektriniai įrankiai', 'slug' => 'elektriniai-irankiai']);
        $categoryB = Category::factory()->create(['name' => 'Saugos priemonės', 'slug' => 'saugos-priemones']);

        $productA = Product::factory()->for($brandA)->create([
            'name' => 'Profesionalus perforatorius',
            'slug' => 'profesionalus-perforatorius',
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $productA->categories()->attach($categoryA->id);
        Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id' => $productA->id,
            'currency_id' => 1,
            'amount' => 199.99,
            'is_enabled' => true,
        ]);

        $productB = Product::factory()->for($brandB)->create([
            'name' => 'Apsauginis šalmas',
            'slug' => 'apsauginis-salmas',
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $productB->categories()->attach($categoryB->id);
        Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id' => $productB->id,
            'currency_id' => 1,
            'amount' => 49.50,
            'is_enabled' => true,
        ]);

        $this->get(route('frontend.products.index'))
            ->assertOk()
            ->assertSeeText($productA->name)
            ->assertSeeText($productB->name);

        $this->get(route('frontend.products.index', ['category' => $categoryA->slug]))
            ->assertOk()
            ->assertSeeText($productA->name)
            ->assertDontSeeText($productB->name);

        $this->get(route('frontend.products.index', ['brand' => $brandB->slug]))
            ->assertOk()
            ->assertSeeText($productB->name)
            ->assertDontSeeText($productA->name);

        $this->get(route('frontend.products.index', ['q' => 'perforatorius']))
            ->assertOk()
            ->assertSeeText($productA->name)
            ->assertDontSeeText($productB->name);
    }
}
