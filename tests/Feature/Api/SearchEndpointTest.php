<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SearchEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_ranks_exact_name_hits_first(): void
    {
        $brand = Brand::factory()->create(['name' => 'Acme']);
        $category = Category::factory()->create(['name' => 'Widgets']);

        $exactProduct = Product::factory()
            ->hasAttached($category, [], 'categories')
            ->create([
                'name'              => 'Precision Hammer',
                'short_description' => 'High quality hammer for precise work',
                'brand_id'          => $brand->id,
                'price'             => 120,
                'is_visible'        => true,
                'published_at'      => now()->subDay(),
            ]);

        Product::factory()
            ->hasAttached($category, [], 'categories')
            ->create([
                'name'         => 'Heavy Duty Hammer',
                'description'  => 'Durable hammer suited for industrial jobs',
                'brand_id'     => $brand->id,
                'price'        => 80,
                'is_visible'   => true,
                'published_at' => now()->subDays(2),
            ]);

        $response = $this->getJson('/api/v1/search?q=Precision%20Hammer');

        $response->assertOk();
        $payload = $response->json('data.products.items');
        self::assertIsArray($payload);
        self::assertSame('Precision Hammer', $payload[0]['title']);
        self::assertSame($exactProduct->id, $payload[0]['id']);
    }

    public function test_search_endpoint_is_resilient_to_injection_sequences(): void
    {
        Product::factory()->create([
            'name'         => 'Injection Safe Product',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/search?q=' . urlencode("Injection Safe' OR 1=1 --"));
        $response->assertOk();

        $payload = $response->json('data.products.items');
        self::assertIsArray($payload);
        self::assertNotEmpty($payload);
        foreach ($payload as $item) {
            self::assertStringNotContainsString('1=1', $item['title']);
        }
    }

    public function test_search_endpoint_enforces_per_page_cap(): void
    {
        Product::factory()->count(5)->create([
            'name'         => 'Cap Test Product',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/search?q=Cap%20Test%20Product&per_page=500');
        $response->assertOk();
        self::assertSame(50, $response->json('meta.per_page'));
    }

    public function test_query_plan_uses_indexes_when_available(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            self::markTestSkipped('SQLite query planner does not expose index usage for wildcard LIKE operations.');
        }

        DB::statement('CREATE INDEX IF NOT EXISTS products_name_idx ON products (name)');

        Product::factory()->create([
            'name'         => 'Planner Product',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $builder = Product::query()->select('id')->where('name', 'like', 'Planner%');
        $plan = DB::select('EXPLAIN ' . $builder->toSql(), $builder->getBindings());

        $planString = json_encode($plan, JSON_THROW_ON_ERROR);
        self::assertStringContainsString('INDEX', strtoupper($planString));
    }
}
