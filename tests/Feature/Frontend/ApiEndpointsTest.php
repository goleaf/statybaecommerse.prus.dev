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
