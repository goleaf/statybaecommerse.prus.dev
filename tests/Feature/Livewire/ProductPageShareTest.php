<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\ProductPage;
use App\Models\Product;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductPageShareTest extends TestCase
{
    /**
     * Verify the ProductPage share action dispatches the browser event with a fallback URL.
     */
    public function test_share_event_contains_expected_product_url(): void
    {
        // Build an in-memory product so the Livewire component receives localized attributes without hitting the database.
        $product = Product::factory()->make([
            'slug'         => 'test-product',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
            'brand_id'     => null,
        ]);

        // Ensure Livewire can encrypt payloads during the test run.
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // Guard assertions that assume the route key and slug stay aligned.
        self::assertSame('test-product', $product->slug);
        self::assertSame('test-product', $product->getRouteKey());

        // Mount the component using the transient product instance.
        $component = Livewire::test(ProductPage::class, ['product' => $product]);

        // Trigger the share action which should dispatch the browser event.
        $component->call('shareProduct');

        // Collect the dispatched events from the Livewire test harness.
        $dispatches = collect(data_get($component->effects, 'dispatches'));

        self::assertContains('share-product', $dispatches->pluck('name'));
        self::assertSame('test-product', $component->instance()->productRouteKey);

        $dispatch = $dispatches->firstWhere('name', 'share-product');
        self::assertNotNull($dispatch);

        $payload = $dispatch['params'][0] ?? [];

        $routeKey = $product->getRouteKey()
            ?: ($product->slug ?? $product->getAttribute($product->getRouteKeyName()));

        if (empty($routeKey) && $product->exists) {
            $routeKey = (string) $product->getKey();
        }

        $expectedUrl = null;

        if (Route::has('localized.products.show')) {
            try {
                $expectedUrl = route('localized.products.show', [
                    'locale'  => app()->getLocale(),
                    'product' => $routeKey,
                ]);
            } catch (UrlGenerationException) {
                // Ignore and fall back to alternative routes for the share URL.
            }
        }

        if (! $expectedUrl && Route::has('frontend.products.show')) {
            try {
                $expectedUrl = route('frontend.products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Continue to the remaining fallbacks.
            }
        }

        if (! $expectedUrl && Route::has('products.show')) {
            try {
                $expectedUrl = route('products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Skip and fallback to the manual URL builder.
            }
        }

        $expectedUrl ??= url(sprintf('/products/%s', $routeKey));

        if ($routeKey && ! str_contains($expectedUrl, (string) $routeKey)) {
            $expectedUrl = url(sprintf('/products/%s', $routeKey));
        }

        self::assertSame($expectedUrl, $payload['url'] ?? null);
    }
}
