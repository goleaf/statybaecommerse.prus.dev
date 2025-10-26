<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Data\SearchQueryData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Search\ProductSearchRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_prioritises_exact_matches(): void
    {
        $brand = Brand::factory()->create(['name' => 'Focus Brand', 'is_enabled' => true]);

        Product::factory()->create([
            'name'         => 'Alpha Hammer',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        Product::factory()->create([
            'name'         => 'Alpha Hammer Deluxe',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson(route('api.v1.search', ['query' => 'Alpha Hammer']));

        $response->assertOk();
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertSame('Alpha Hammer', $data[0]['title']);
        $this->assertGreaterThanOrEqual($data[1]['ranking_score'] ?? 0, $data[0]['ranking_score']);
    }

    public function test_search_endpoint_is_safe_against_sql_injection_attempts(): void
    {
        $brand = Brand::factory()->create(['name' => 'Secure Brand', 'is_enabled' => true]);

        Product::factory()->create([
            'name'         => 'Secure Drill',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        Product::factory()->create([
            'name'         => 'Unrelated Item',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $payload = [
            'query'    => "Secure%' OR 1=1 --",
            'per_page' => 10,
        ];

        $response = $this->getJson(route('api.v1.search', $payload));

        $response->assertOk();
        $data = collect($response->json('data'));

        $this->assertTrue($data->isEmpty(), 'Injection attempt should not leak unrelated data.');
    }

    public function test_search_endpoint_caps_per_page_requests(): void
    {
        $brand = Brand::factory()->create(['name' => 'Limit Brand', 'is_enabled' => true]);
        $category = Category::factory()->create(['name' => 'Limit Category', 'is_visible' => true]);

        $product = Product::factory()->create([
            'name'         => 'Limit Product',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);
        $category->products()->attach($product);

        $response = $this->getJson(route('api.v1.search', ['query' => 'Limit', 'per_page' => 999]));

        $response->assertOk();
        $meta = $response->json('meta');

        $this->assertSame(SearchQueryData::MAX_PER_PAGE, $meta['per_page']);
        $this->assertSame(SearchQueryData::MAX_PER_PAGE, $meta['max_per_page']);
    }

    public function test_search_endpoint_rejects_empty_queries(): void
    {
        $this->getJson(route('api.v1.search', ['query' => '']))
            ->assertStatus(422);
    }

    public function test_search_endpoint_rejects_overly_long_queries(): void
    {
        $this->getJson(route('api.v1.search', ['query' => str_repeat('x', 256)]))
            ->assertStatus(422);
    }

    public function test_product_repository_exposes_query_plan(): void
    {
        $repository = app(ProductSearchRepository::class);
        $queryData = SearchQueryData::fromArray([
            'query'    => 'plan',
            'page'     => 1,
            'per_page' => 5,
        ], ['locale' => app()->getLocale()]);

        $plan = $repository->explain($queryData, 5);

        $this->assertNotEmpty($plan);
        $this->assertIsArray($plan);
    }
}
