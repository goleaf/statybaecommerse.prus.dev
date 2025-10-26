<?php

declare(strict_types=1);

use App\Livewire\ProductPage;
use App\Models\Product;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);

it('dispatches share event with the expected product url', function (): void {
    $product = Product::factory()->make([
        'slug'         => 'test-product',
        'status'       => 'published',
        'is_visible'   => true,
        'published_at' => now(),
        'brand_id'     => null,
    ]);

    config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

    expect($product->slug)->toBe('test-product');
    expect($product->getRouteKey())->toBe('test-product');

    $component = Livewire::test(ProductPage::class, ['product' => $product]);

    $component->call('shareProduct');

    expect(collect(data_get($component->effects, 'dispatches'))->pluck('name'))->toContain('share-product');

    expect($component->instance()->productRouteKey)->toBe('test-product');

    $dispatch = collect(data_get($component->effects, 'dispatches'))
        ->firstWhere('name', 'share-product');

    expect($dispatch)->not->toBeNull();

    $payload = $dispatch['params'][0] ?? [];

    $routeKey = $product->getRouteKey() ?: ($product->slug ?? $product->getAttribute($product->getRouteKeyName()));

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
            // Ignore and fall back to non-localized routes.
        }
    }

    if (! $expectedUrl && Route::has('frontend.products.show')) {
        try {
            $expectedUrl = route('frontend.products.show', ['product' => $routeKey]);
        } catch (UrlGenerationException) {
            // Ignore and continue falling back.
        }
    }

    if (! $expectedUrl && Route::has('products.show')) {
        try {
            $expectedUrl = route('products.show', ['product' => $routeKey]);
        } catch (UrlGenerationException) {
            // Ignore and continue falling back.
        }
    }

    $expectedUrl ??= url(sprintf('/products/%s', $routeKey));

    if ($routeKey && ! str_contains($expectedUrl, (string) $routeKey)) {
        $expectedUrl = url(sprintf('/products/%s', $routeKey));
    }

    expect($payload['url'] ?? null)->toBe($expectedUrl);
});
