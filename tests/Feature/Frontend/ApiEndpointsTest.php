<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

/**
 * @internal
 */
final class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_products_returns_media_attributes(): void
    {
        // Arrange: create a visible product with media so the API has data to expose.
        $visibleProduct = Product::factory()
            ->published()
            ->create([
                'name' => 'Test Searchable Product',
            ]);

        ProductImage::factory()
            ->for($visibleProduct)
            ->create([
                'path'       => 'product-images/test-search-product.jpg',
                'sort_order' => 0,
            ]);

        // Arrange: ensure drafts never leak into the storefront endpoint.
        Product::factory()->create([
            'name'         => 'Hidden Product',
            'status'       => 'draft',
            'is_visible'   => false,
            'published_at' => null,
        ]);

        // Act: hit the search endpoint with a query that should match the product.
        $response = $this->getJson(route('frontend.api.products.search', ['q' => 'Searchable']));

        // Assert: validate HTTP response and ensure media keys are present.
        $response->assertOk();

        $payload = $response->json();

        self::assertIsArray($payload);
        self::assertNotEmpty($payload);

        $result = array_values($payload)[0];
        self::assertIsArray($result);

        // Assert: only the published product should be returned by the API.
        self::assertSame($visibleProduct->getKey(), $result['id']);
        self::assertArrayHasKey('main_image', $result);
        self::assertArrayHasKey('thumbnail', $result);
        self::assertArrayHasKey('image', $result);

        $visibleProduct = $visibleProduct->fresh();
        self::assertInstanceOf(Product::class, $visibleProduct);

        self::assertSame($visibleProduct->main_image, $result['main_image']);
        // Confirm the legacy `image` key mirrors the new main image accessor.
        self::assertSame($visibleProduct->main_image, $result['image']);
        self::assertSame($visibleProduct->thumbnail, $result['thumbnail']);
    }

    public function test_recently_viewed_products_return_media_attributes(): void
    {
        // Arrange: seed two products to mimic user history order.
        $firstProduct = Product::factory()
            ->published()
            ->create([
                'name' => 'First Viewed Product',
            ]);

        ProductImage::factory()
            ->for($firstProduct)
            ->create([
                'path'       => 'product-images/first-viewed.jpg',
                'sort_order' => 0,
            ]);

        $secondProduct = Product::factory()
            ->published()
            ->create([
                'name' => 'Second Viewed Product',
            ]);

        ProductImage::factory()
            ->for($secondProduct)
            ->create([
                'path'       => 'product-images/second-viewed.jpg',
                'sort_order' => 0,
            ]);

        // Act: emulate a session history where the second product was viewed last.
        $response = $this->withSession([
            'recently_viewed' => [$secondProduct->id, $firstProduct->id],
        ])->getJson(route('frontend.api.recently-viewed'));

        // Assert: response order should respect the session and include media fields.
        $response->assertOk();

        $payload = $response->json();

        self::assertIsArray($payload);
        self::assertCount(2, $payload);

        $firstResult = array_values($payload)[0];
        self::assertIsArray($firstResult);

        self::assertSame($secondProduct->id, $firstResult['id']);
        self::assertArrayHasKey('main_image', $firstResult);
        self::assertArrayHasKey('thumbnail', $firstResult);
        self::assertArrayHasKey('image', $firstResult);

        $secondProduct = $secondProduct->fresh();
        self::assertInstanceOf(Product::class, $secondProduct);

        self::assertSame($secondProduct->main_image, $firstResult['main_image']);
        // The compatibility alias should reflect the same media accessor as `main_image`.
        self::assertSame($secondProduct->main_image, $firstResult['image']);
        self::assertSame($secondProduct->thumbnail, $firstResult['thumbnail']);
    }
}
