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

final class BrandFrontendTest extends TestCase
{
    use RefreshDatabase;

    private function createCurrency(): void
    {
        Currency::factory()->eur()->default()->create(['id' => 1]);
    }

    public function test_brand_index_lists_brands(): void
    {
        $brand = Brand::factory()->create(['name' => 'Hilti Lithuania', 'is_enabled' => true, 'is_visible' => true]);

        $this->get(route('frontend.brands.index'))
            ->assertOk()
            ->assertSeeText($brand->name);
    }

    public function test_brand_show_displays_products(): void
    {
        $this->createCurrency();

        $brand = Brand::factory()->create(['name' => 'Festool Baltic', 'is_enabled' => true, 'is_visible' => true]);
        $category = Category::factory()->create();

        $product = Product::factory()->for($brand)->create([
            'name'         => 'Festool dulkių siurblys',
            'slug'         => 'festool-dulkiu-siurblys',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);
        $product->categories()->attach($category->id);

        Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->id,
            'currency_id'    => 1,
            'amount'         => 349.00,
            'is_enabled'     => true,
        ]);

        $this->get(route('frontend.brands.show', $brand))
            ->assertOk()
            ->assertSeeText($brand->name)
            ->assertSeeText($product->name);
    }
}
