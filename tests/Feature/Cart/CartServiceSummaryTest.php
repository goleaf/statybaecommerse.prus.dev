<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart\CartService;

it('builds summaries from persistent cart items including variant metadata and discounted shipping thresholds', function (): void {
    // Ensure deterministic configuration so the assertions capture the recalculated totals precisely.
    config()->set('shared.shipping.free_threshold', 50.0);
    config()->set('shared.shipping.flat_rate', 5.99);
    config()->set('shared.tax.default_rate', 0.21);

    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->getKey(),
        'price' => 60.00,
    ]);

    $sessionId = 'sess-db-summary';

    // Seed a persisted cart row that carries explicit variant metadata and attribute payloads.
    CartItem::factory()->create([
        'session_id' => $sessionId,
        'user_id' => $user->getKey(),
        'product_id' => $product->getKey(),
        'variant_id' => $variant->getKey(),
        'product_variant_id' => $variant->getKey(),
        'quantity' => 1,
        'unit_price' => 60.00,
        'price' => 60.00,
        'total_price' => 60.00,
        'attributes' => [
            'size' => 'L',
            'variant_id' => $variant->getKey(),
            'custom' => 'engraving',
        ],
        'product_snapshot' => [
            'name' => $product->name,
            'image' => 'https://example.com/image.jpg',
        ],
    ]);

    session()->put('cart_discount', 15.0);

    $summary = app(CartService::class)->getSummary($user->getKey(), $sessionId);

    expect($summary['items'])->toHaveCount(1)
        ->and($summary['items'][0]['variant_id'])->toBe($variant->getKey())
        // Variant identifiers should be lifted into the dedicated key and removed from the attribute bag.
        ->and($summary['items'][0]['attributes'])->toMatchArray([
            'size' => 'L',
            'custom' => 'engraving',
        ])
        ->and($summary['subtotal'])->toBe(60.0)
        // Discounted subtotal (60 - 15) falls below the threshold so shipping should apply.
        ->and($summary['shipping'])->toBe(5.99)
        // Tax should apply to the discounted subtotal (45 * 0.21 = 9.45).
        ->and($summary['tax'])->toBe(9.45)
        // Totals reflect discounted subtotal plus tax and shipping.
        ->and($summary['total'])->toBe(60.44);
});

it('normalizes session cart payloads within configured quantity limits and clamps excessive discounts', function (): void {
    config()->set('shared.cart.min_quantity', 1);
    config()->set('shared.cart.max_quantity', 5);

    session()->flush();

    // Simulate raw session items with invalid negative and extreme quantities.
    session()->put('cart', [
        [
            'product_id' => 101,
            'variant_id' => 202,
            'name' => 'Example Item',
            'price' => 10.0,
            'quantity' => -3,
            'attributes' => ['color' => 'Red'],
        ],
        [
            'product_id' => 102,
            'variant_id' => 203,
            'name' => 'Bulk Item',
            'price' => 2.5,
            'quantity' => 99,
            'attributes' => ['size' => 'XL'],
        ],
    ]);

    session()->put('cart_discount', 999.0);

    $summary = app(CartService::class)->getSummary(null, session()->getId());

    expect($summary['items'][0]['quantity'])->toBe(1)
        ->and($summary['items'][1]['quantity'])->toBe(5)
        // Attributes should flow through untouched for downstream personalization.
        ->and($summary['items'][0]['attributes'])->toMatchArray(['color' => 'Red'])
        ->and($summary['items'][1]['attributes'])->toMatchArray(['size' => 'XL'])
        // Discount cannot exceed the computed subtotal, ensuring totals never go negative.
        ->and($summary['discount'])->toBe($summary['subtotal'])
        ->and($summary['total'])->toBe(0.0)
        ->and($summary['shipping'])->toBe(0.0)
        ->and($summary['tax'])->toBe(0.0);
});
