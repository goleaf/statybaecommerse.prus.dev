<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_avoids_n_plus_one_queries(): void
    {
        Cache::flush();
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()
            ->count(3)
            ->sequence(fn (int $index) => [
                'name' => 'Elektrinis įrankis '.($index + 1),
                'slug' => 'elektrinis-irankis-'.($index + 1),
                'sku' => 'SKU-'.($index + 1),
                'brand_id' => $brand->id,
                'is_visible' => true,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'price' => 100 + $index,
            ])
            ->create()
            ->each(fn (Product $product) => $product->categories()->attach($category->id));

        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->getJson(route('api.products.search', ['q' => 'Elektrinis', 'limit' => 5]));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $this->assertLessThanOrEqual(6, count($queries), 'Search endpoint executed too many queries: '.count($queries));
    }
}
