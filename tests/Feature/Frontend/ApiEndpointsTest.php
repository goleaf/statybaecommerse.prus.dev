<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_products_returns_media_attributes(): void
    {
        // Create a published product so it is discoverable via the scoped product query.
        $product = Product::factory()->create([
            'name'         => 'Test Searchable Product',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        // Attach a product image to populate the computed main_image/thumbnail attributes.
        ProductImage::factory()->for($product)->create([
            'path'       => 'product-images/test-search-product.jpg',
            'sort_order' => 0,
        ]);

        // Issue the frontend search request and capture the payload for assertions.
        $response = $this->getJson(route('frontend.api.products.search', ['q' => 'Searchable']));
        $response->assertOk();

        $payload = $response->json();
        self::assertIsArray($payload);
        self::assertNotEmpty($payload);

        $result = array_values($payload)[0];
        self::assertIsArray($result);
        /** @var array<string, mixed> $result */
        $result = $result;

        // Ensure the normalized media keys are present while the deprecated image key is absent.
        self::assertArrayHasKey('main_image', $result);
        self::assertArrayHasKey('thumbnail', $result);
        self::assertArrayNotHasKey('image', $result);

        $product = $product->fresh();
        self::assertInstanceOf(Product::class, $product);

        // Confirm that the computed media paths match what the API returned.
        self::assertSame($product->main_image, $result['main_image']);
        self::assertSame($product->thumbnail, $result['thumbnail']);
    }

    public function test_recently_viewed_products_return_media_attributes(): void
    {
        // Create two published products with media so the recently viewed endpoint has data to hydrate.
        $firstProduct = Product::factory()->create([
            'name'         => 'First Viewed Product',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);
        ProductImage::factory()->for($firstProduct)->create([
            'path'       => 'product-images/first-viewed.jpg',
            'sort_order' => 0,
        ]);

        $secondProduct = Product::factory()->create([
            'name'         => 'Second Viewed Product',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);
        ProductImage::factory()->for($secondProduct)->create([
            'path'       => 'product-images/second-viewed.jpg',
            'sort_order' => 0,
        ]);

        // Seed the session so that the endpoint returns both items in the expected order.
        $response = $this->withSession([
            'recently_viewed' => [$secondProduct->id, $firstProduct->id],
        ])->getJson(route('frontend.api.recently-viewed'));
        $response->assertOk();

        $payload = $response->json();
        self::assertIsArray($payload);
        self::assertCount(2, $payload);

        $firstResult = array_values($payload)[0];
        self::assertIsArray($firstResult);
        /** @var array<string, mixed> $firstResult */
        $firstResult = $firstResult;

        // The latest viewed product should be first and should expose normalized media fields.
        self::assertSame($secondProduct->id, $firstResult['id']);
        self::assertArrayHasKey('main_image', $firstResult);
        self::assertArrayHasKey('thumbnail', $firstResult);
        self::assertArrayNotHasKey('image', $firstResult);

        $secondProduct = $secondProduct->fresh();
        self::assertInstanceOf(Product::class, $secondProduct);

        // Confirm the API payload mirrors the product's computed media attributes.
        self::assertSame($secondProduct->main_image, $firstResult['main_image']);
        self::assertSame($secondProduct->thumbnail, $firstResult['thumbnail']);
    }
}
