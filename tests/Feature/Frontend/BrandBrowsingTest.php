<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
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
            ->assertViewIs('frontend.brands.index')
            ->assertSee('Makita Tools LT');
    }

    public function test_brand_show_lists_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Bosch Lietuva']);
        $product = $this->createPublishedProduct(['brand_id' => $brand->id, 'name' => 'Bosch Drill', 'slug' => 'bosch-drill']);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertViewHas('products', function ($paginator) use ($product) {
                return $paginator->contains('id', $product->id);
            });
    }

    public function test_brand_show_paginates_products_by_twelve_and_renders_bottom_pager(): void
    {
        $brand = Brand::factory()->create(['name' => 'Wkret-Met']);

        foreach (range(1, 13) as $index) {
            $this->createPublishedProduct([
                'brand_id' => $brand->id,
                'name' => "Wkret Product {$index}",
                'slug' => "wkret-product-{$index}",
                'published_at' => Carbon::now()->subMinutes($index),
            ]);
        }

        $firstPageResponse = $this->get(route('frontend.brands.show', $brand));

        $firstPageResponse->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertViewHas('products', static function ($paginator): bool {
                return $paginator->perPage() === 12
                    && $paginator->total() === 13
                    && $paginator->count() === 12
                    && $paginator->currentPage() === 1
                    && $paginator->hasPages();
            })
            ->assertSee('page=2', false);

        $secondPageResponse = $this->get(route('frontend.brands.show', $brand) . '?page=2');

        $secondPageResponse->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertViewHas('products', static function ($paginator): bool {
                return $paginator->perPage() === 12
                    && $paginator->total() === 13
                    && $paginator->count() === 1
                    && $paginator->currentPage() === 2;
            });
    }

    public function test_brand_show_displays_message_when_no_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Empty Brand']);

        $response = $this->get(route('frontend.brands.show', $brand));

        $response->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertSee(__('messages.no_products_brand'));
    }

    public function test_brand_show_in_stock_filter_only_returns_in_stock_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Stock Filter Brand']);
        $inStockProduct = $this->createPublishedProduct([
            'brand_id'       => $brand->id,
            'name'           => 'Available Drill',
            'slug'           => 'available-drill',
            'manage_stock'   => true,
            'stock_quantity' => 5,
        ]);
        $outOfStockProduct = $this->createPublishedProduct([
            'brand_id'       => $brand->id,
            'name'           => 'Unavailable Drill',
            'slug'           => 'unavailable-drill',
            'manage_stock'   => true,
            'stock_quantity' => 0,
        ]);

        $response = $this->get(route('frontend.brands.show', $brand) . '?filter=in_stock');

        $response->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertViewHas('products', function ($paginator) use ($inStockProduct, $outOfStockProduct) {
                return $paginator->contains('id', $inStockProduct->id)
                    && ! $paginator->contains('id', $outOfStockProduct->id);
            });
    }

    public function test_brand_show_featured_filter_does_not_fallback_to_category_sections(): void
    {
        $brand = Brand::factory()->create(['name' => 'Featured Filter Brand']);
        $category = Category::factory()->create();
        $regularProduct = $this->createPublishedProduct([
            'brand_id'     => $brand->id,
            'name'         => 'Regular Product',
            'slug'         => 'regular-product',
            'is_featured'  => false,
            'stock_quantity' => 10,
        ]);
        $regularProduct->categories()->attach($category->id);

        $response = $this->get(route('frontend.brands.show', $brand) . '?filter=featured');

        $response->assertOk()
            ->assertViewIs('frontend.brands.show')
            ->assertViewHas('products', static fn ($paginator): bool => $paginator->isEmpty())
            ->assertViewHas('categoryProductSections', static fn ($sections): bool => $sections->isEmpty())
            ->assertSee(__('messages.no_products_brand'));
    }

    private function createPublishedProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }
}
