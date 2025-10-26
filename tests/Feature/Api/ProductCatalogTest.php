<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\Api\ProductSort;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginates_and_respects_filters(): void
    {
        // Prepare the catalogue with two categories so filtering can exclude the non-matching record.
        $matchingCategory = Category::factory()->create(['is_visible' => true]);
        $otherCategory = Category::factory()->create(['is_visible' => true]);

        // Create a visible, published product that should survive the filter criteria.
        $includedProduct = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'price' => 49,
        ]);
        $includedProduct->categories()->attach($matchingCategory->getKey());

        // Create a product that is filtered out because of price and category.
        $excludedProduct = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'price' => 199,
        ]);
        $excludedProduct->categories()->attach($otherCategory->getKey());

        // Issue the index request with explicit filters to exercise the request object and enum sorting.
        $response = $this->getJson(route('api.products.index', [
            'category' => $matchingCategory->slug,
            'price_max' => 100,
            'per_page' => 1,
            'sort' => ProductSort::PRICE_ASC->value,
        ]));

        $response->assertOk();
        $payload = $response->json();

        // Confirm that pagination metadata reflects the capped per-page limit and overall totals.
        $this->assertSame(1, $payload['meta']['pagination']['per_page']);
        $this->assertSame(1, $payload['meta']['pagination']['total']);
        $this->assertSame($matchingCategory->slug, $payload['meta']['filters']['category']);

        // Only the filtered product should be present in the dataset.
        $this->assertCount(1, $payload['data']);
        $this->assertSame($includedProduct->getKey(), $payload['data'][0]['id']);
    }

    public function test_show_returns_consistent_schema_with_etag_support(): void
    {
        // Create supporting resources to hydrate the eager-loaded relations for the response payload.
        $brand = Brand::factory()->create(['is_visible' => true]);
        $category = Category::factory()->create(['is_visible' => true]);

        // Set up a published product with a variant so nested resources are exercised.
        $product = Product::factory()->for($brand)->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'price' => 99,
        ]);
        $product->categories()->attach($category->getKey());
        ProductVariant::factory()->for($product)->create([
            'is_default' => true,
            'price' => 99,
        ]);

        $response = $this->getJson(route('api.products.show', ['product' => $product]));
        $response->assertOk();

        $etag = $response->headers->get('ETag');
        $this->assertNotNull($etag, 'Expected ETag header to be set for conditional caching.');

        $payload = $response->json();
        $this->assertSame($product->getKey(), $payload['data']['id']);
        $this->assertSame($brand->name, $payload['data']['brand']['name']);
        $this->assertSame($category->slug, $payload['data']['categories'][0]['slug']);
        $this->assertNotEmpty($payload['data']['variants']);

        // Repeat the request with the returned ETag to ensure conditional GET short-circuits to 304.
        $cachedResponse = $this->getJson(route('api.products.show', ['product' => $product]), [
            'If-None-Match' => $etag,
        ]);
        $cachedResponse->assertStatus(304);
    }

    public function test_invalid_filter_returns_unprocessable_entity(): void
    {
        // Supply an unsupported sort option so the enum-based validation triggers a 422 response.
        $response = $this->getJson(route('api.products.index', ['sort' => 'DROP TABLE']));
        $response->assertStatus(422);
    }
}
