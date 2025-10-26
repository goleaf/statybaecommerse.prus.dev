<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\ProductPage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductPageQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_uses_optimized_query_count(): void
    {
        $brand = Brand::factory()->create([
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $category = Category::factory()->create([
            'is_enabled' => true,
        ]);

        $product = Product::factory()->create([
            'type'                => 'simple',
            'brand_id'            => $brand->id,
            'is_visible'          => true,
            'status'              => 'published',
            'published_at'        => now()->subDay(),
            'manage_stock'        => true,
            'stock_quantity'      => 25,
            'low_stock_threshold' => 5,
            'price'               => 199.00,
        ]);

        $product->categories()->attach($category);

        ProductVariant::factory()->for($product)->create([
            'price'           => 189.00,
            'track_inventory' => false,
            'is_default'      => true,
        ]);

        ProductVariant::factory()->for($product)->create([
            'price'           => 209.00,
            'track_inventory' => false,
        ]);

        Review::factory()->for($product)->create([
            'rating'      => 5,
            'is_approved' => true,
        ]);

        Review::factory()->for($product)->create([
            'rating'      => 3,
            'is_approved' => true,
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        Livewire::test(ProductPage::class, ['product' => $product->fresh()]);

        $this->assertLessThanOrEqual(170, count(DB::getQueryLog()), print_r(DB::getQueryLog(), true));
    }
}
