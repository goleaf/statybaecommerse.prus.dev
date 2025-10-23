<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class BrandBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_index_lists_brands(): void
    {
        $brand = Brand::factory()->create(['name' => 'Makita Tools LT']);
        $this->createPublishedProduct(['brand_id' => $brand->id]);

        $response = $this->get(route('frontend.brands.index'));

        $response->assertOk()
            ->assertViewIs('brands.index')
            ->assertSee('Makita Tools LT');
    }

    public function test_brand_show_lists_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Bosch Lietuva']);
        $product = $this->createPublishedProduct(['brand_id' => $brand->id, 'name' => 'Bosch Drill', 'slug' => 'bosch-drill']);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk()
            ->assertViewIs('brands.show')
            ->assertViewHas('products', function ($paginator) use ($product) {
                return $paginator->contains('id', $product->id);
            });
    }

    public function test_brand_show_displays_message_when_no_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Empty Brand']);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk()
            ->assertViewIs('brands.show')
            ->assertSee('No products found for this brand yet.');
    }

    private function createPublishedProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'published',
            'is_visible' => true,
            'is_enabled' => true,
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }
}
