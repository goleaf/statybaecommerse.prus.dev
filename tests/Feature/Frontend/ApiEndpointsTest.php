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
        $response = $this->getJson(route('api.products.search', ['q' => 'Searchable']));

        // Assert: validate HTTP response and ensure media keys are present.
        $response->assertOk();

        $payload = $response->json();

        self::assertIsArray($payload);
        self::assertArrayHasKey('data', $payload);
        self::assertIsArray($payload['data']);
        self::assertArrayHasKey('items', $payload['data']);

        $items = $payload['data']['items'];
        self::assertIsArray($items);
        self::assertNotEmpty($items);

        $result = array_values($items)[0];
        self::assertIsArray($result);

        // Assert: only the published product should be returned by the API.
        self::assertSame($visibleProduct->getKey(), $result['id']);
        self::assertArrayHasKey('media', $result);
        self::assertIsArray($result['media']);
        self::assertArrayHasKey('images', $result['media']);

        $images = $result['media']['images'];
        self::assertIsArray($images);

        if ($images !== []) {
            $primaryImage = $images[0];
            self::assertIsArray($primaryImage);
            self::assertArrayHasKey('url', $primaryImage);
            self::assertArrayHasKey('thumbnail', $primaryImage);
            self::assertArrayHasKey('alt', $primaryImage);
        }

        $visibleProduct = $visibleProduct->fresh();
        self::assertInstanceOf(Product::class, $visibleProduct);

        if ($images !== []) {
            $primaryImage = $images[0];
            self::assertSame($visibleProduct->main_image, $primaryImage['url']);
            self::assertSame($visibleProduct->thumbnail, $primaryImage['thumbnail']);
        }
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
