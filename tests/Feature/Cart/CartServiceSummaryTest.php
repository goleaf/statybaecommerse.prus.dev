<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart\CartService;

it('builds summaries from persistent cart items including variant metadata and tax calculations', function (): void {
    // Ensure deterministic configuration so the assertions capture the recalculated totals precisely.
    config()->set('shared.tax.default_rate', 0.0);

    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->getKey(),
        'price'      => 60.00,
    ]);

    $sessionId = 'sess-db-summary';

    // Seed a persisted cart row that carries explicit variant metadata and attribute payloads.
    CartItem::factory()->create([
        'session_id'         => $sessionId,
        'user_id'            => $user->getKey(),
        'product_id'         => $product->getKey(),
        'variant_id'         => $variant->getKey(),
        'product_variant_id' => $variant->getKey(),
        'quantity'           => 1,
        'unit_price'         => 60.00,
        'price'              => 60.00,
        'total_price'        => 60.00,
        'attributes'         => [
            'size'       => 'L',
            'variant_id' => $variant->getKey(),
            'custom'     => 'engraving',
        ],
        'product_snapshot' => [
            'name'  => $product->name,
            'image' => 'https://example.com/image.jpg',
        ],
    ]);

    session()->put('cart_discount', 15.0);

    $summary = app(CartService::class)->getSummary($user->getKey(), $sessionId);

    expect($summary['items'])->toHaveCount(1)
        ->and($summary['items'][0]['variant_id'])->toBe($variant->getKey())
        // Variant identifiers should be lifted into the dedicated key and removed from the attribute bag.
        ->and($summary['items'][0]['attributes'])->toMatchArray([
            'size'   => 'L',
            'custom' => 'engraving',
        ])
        ->and($summary['subtotal'])->toBe(60.0)
        // Shipping is no longer calculated
        ->and($summary['shipping'])->toBe(0.0)
        // Tax should be 0.0 with the new rate.
        ->and($summary['tax'])->toBe(0.0)
        // Totals reflect discounted subtotal with no tax.
        ->and($summary['total'])->toBe(45.0);
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
            'name'       => 'Example Item',
            'price'      => 10.0,
            'quantity'   => -3,
            'attributes' => ['color' => 'Red'],
        ],
        [
            'product_id' => 102,
            'variant_id' => 203,
            'name'       => 'Bulk Item',
            'price'      => 2.5,
            'quantity'   => 99,
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

it('falls back to the product main image when persisted cart snapshots have no image', function (): void {
    config()->set('shared.tax.default_rate', 0.0);

    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductImage::factory()->for($product, 'product')->create([
        'path'       => 'product-images/cart-db-fallback.jpg',
        'is_default' => true,
        'sort_order' => 1,
    ]);

    $sessionId = 'sess-db-image-fallback';

    CartItem::factory()->create([
        'session_id'       => $sessionId,
        'user_id'          => $user->getKey(),
        'product_id'       => $product->getKey(),
        'quantity'         => 1,
        'unit_price'       => 25.0,
        'price'            => 25.0,
        'total_price'      => 25.0,
        'product_snapshot' => [
            'name' => $product->name,
            'sku'  => $product->sku,
        ],
    ]);

    $summary = app(CartService::class)->getSummary($user->getKey(), $sessionId);

    expect($summary['items'])->toHaveCount(1)
        ->and($summary['items'][0]['image'])->toBe($product->fresh()->main_image);
});

it('hydrates missing session cart images from product default images', function (): void {
    config()->set('shared.tax.default_rate', 0.0);

    $product = Product::factory()->create();
    ProductImage::factory()->for($product, 'product')->create([
        'path'       => 'product-images/cart-session-fallback.jpg',
        'is_default' => true,
        'sort_order' => 1,
    ]);

    session()->flush();
    session()->put('cart', [
        [
            'id'         => 1,
            'product_id' => $product->getKey(),
            'name'       => $product->name,
            'price'      => 12.5,
            'quantity'   => 2,
            'image'      => null,
        ],
    ]);

    $summary = app(CartService::class)->getSummary(null, session()->getId());

    expect($summary['items'])->toHaveCount(1)
        ->and($summary['items'][0]['image'])->toBe($product->fresh()->main_image);
});
