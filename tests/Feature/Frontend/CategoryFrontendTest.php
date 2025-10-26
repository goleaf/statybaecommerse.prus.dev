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

final class CategoryFrontendTest extends TestCase
{
    use RefreshDatabase;

    private function createCurrency(): void
    {
        Currency::factory()->eur()->default()->create(['id' => 1]);
    }

    public function test_category_index_lists_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Matavimo įranga', 'slug' => 'matavimo-iranga']);

        $this->get(route('frontend.categories.index'))
            ->assertOk()
            ->assertSeeText($category->name);
    }

    public function test_category_show_displays_products(): void
    {
        $this->createCurrency();

        $brand = Brand::factory()->create(['is_visible' => true, 'is_enabled' => true]);
        $category = Category::factory()->create(['name' => 'Ventiliacijos sistemos', 'slug' => 'ventiliacijos-sistemos']);

        $product = Product::factory()->for($brand)->create([
            'name'         => 'Ventiliatoriaus komplektas',
            'slug'         => 'ventiliatoriaus-komplektas',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);
        $product->categories()->attach($category->id);

        Price::factory()->create([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->id,
            'currency_id'    => 1,
            'amount'         => 149.00,
            'is_enabled'     => true,
        ]);

        $this->get(route('frontend.categories.show', $category))
            ->assertOk()
            ->assertSeeText($category->name)
            ->assertSeeText($product->name)
            ->assertSeeText($brand->name);
    }
}
