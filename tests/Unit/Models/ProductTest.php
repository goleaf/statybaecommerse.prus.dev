<?php

declare(strict_types=1);

use App\Contracts\TranslatableRecord;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product model', function () {
    it('implements TranslatableRecord interface', function () {
        $product = new Product;

        expect($product)->toBeInstanceOf(TranslatableRecord::class);
    });

    it('has translations relationship', function () {
        $product = new Product;

        expect($product->translations())->toBeInstanceOf(HasMany::class);
    });

    it('has correct fillable attributes', function () {
        $product = new Product;
        $expectedFillable = [
            'name', 'slug', 'description', 'short_description', 'sku', 'barcode',
            'price', 'compare_price', 'cost_price', 'sale_price', 'manage_stock',
            'track_stock', 'allow_backorder', 'stock_quantity', 'low_stock_threshold',
            'weight', 'length', 'width', 'height', 'is_active', 'is_visible',
            'is_enabled', 'is_featured', 'is_requestable', 'requests_count',
            'minimum_quantity', 'hide_add_to_cart', 'request_message', 'published_at',
            'seo_title', 'seo_description', 'brand_id', 'status', 'type', 'video_url',
            'variant_attribute_matrix', 'sort_order', 'tax_class',
            'shipping_class', 'download_limit', 'download_expiry', 'external_url',
            'button_text',
        ];

        expect($product->getFillable())->toBe($expectedFillable);
    });

    it('has correct casts', function () {
        $product = new Product;
        $casts = $product->getCasts();

        expect($casts)->toHaveKey('price', 'decimal:2')
            ->and($casts)->toHaveKey('is_active', 'boolean')
            ->and($casts)->toHaveKey('published_at', 'datetime')
            ->and($casts)->toHaveKey('deleted_at', 'datetime'); // SoftDeletes adds this
    });

    it('has brand relationship', function () {
        $product = new Product;

        expect($product->brand())->toBeInstanceOf(BelongsTo::class);
    });

    it('has categories relationship', function () {
        $product = new Product;

        expect($product->categories())->toBeInstanceOf(BelongsToMany::class);
    });

    it('has collections relationship', function () {
        $product = new Product;

        expect($product->collections())->toBeInstanceOf(BelongsToMany::class);
    });

    it('has variants relationship', function () {
        $product = new Product;

        expect($product->variants())->toBeInstanceOf(HasMany::class);
    });

    it('has images relationship', function () {
        $product = new Product;

        expect($product->images())->toBeInstanceOf(HasMany::class);
    });

    it('has stock reservations relationship', function () {
        $product = new Product;

        expect($product->stockReservations())->toBeInstanceOf(HasMany::class);
    });

    it('calculates stock status correctly', function () {
        $product = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 5,
        ]);

        expect($product->isInStock())->toBeTrue()
            ->and($product->isLowStock())->toBeFalse()
            ->and($product->isOutOfStock())->toBeFalse()
            ->and($product->getStockStatus())->toBe('in_stock');
    });

    it('handles low stock correctly', function () {
        $product = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 3,
            'low_stock_threshold' => 5,
        ]);

        expect($product->isInStock())->toBeTrue()
            ->and($product->isLowStock())->toBeTrue()
            ->and($product->isOutOfStock())->toBeFalse()
            ->and($product->getStockStatus())->toBe('low_stock');
    });

    it('handles out of stock correctly', function () {
        $product = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 5,
        ]);

        expect($product->isInStock())->toBeFalse()
            ->and($product->isOutOfStock())->toBeTrue()
            ->and($product->getStockStatus())->toBe('out_of_stock');
    });

    it('handles unmanaged stock correctly', function () {
        $product = Product::factory()->create([
            'manage_stock' => false,
        ]);

        expect($product->isInStock())->toBeTrue()
            ->and($product->isLowStock())->toBeFalse()
            ->and($product->isOutOfStock())->toBeFalse()
            ->and($product->getStockStatus())->toBe('not_tracked');
    });

    it('calculates available quantity correctly', function () {
        $product = Product::factory()->create([
            'manage_stock'   => true,
            'stock_quantity' => 100,
        ]);

        expect($product->availableQuantity())->toBe(100);
    });

    it('calculates available quantity with reservations', function () {
        $product = Product::factory()->create([
            'manage_stock'   => true,
            'stock_quantity' => 100,
        ]);

        // Create a stock reservation manually since factory doesn't exist
        $product->stockReservations()->create([
            'quantity'    => 20,
            'status'      => 'reserved',
            'reserved_at' => now(),
            'expires_at'  => now()->addHours(24),
        ]);

        expect($product->availableQuantity())->toBe(80);
    });

    it('checks if product is published', function () {
        $publishedProduct = Product::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'is_visible'   => false,
            'published_at' => now()->subDay(),
        ]);

        $futureProduct = Product::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->addDay(),
        ]);

        expect($publishedProduct->isPublished())->toBeTrue()
            ->and($unpublishedProduct->isPublished())->toBeFalse()
            ->and($futureProduct->isPublished())->toBeFalse();
    });

    it('has correct route key name', function () {
        $product = new Product;

        expect($product->getRouteKeyName())->toBe('slug');
    });

    it('has translatable attributes', function () {
        $product = new Product;
        $expectedTranslatable = ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];

        // Access the protected translatable property using reflection
        $reflection = new ReflectionClass($product);
        $property = $reflection->getProperty('translatable');
        $property->setAccessible(true);

        expect($property->getValue($product))->toBe($expectedTranslatable);
    });

    it('calculates discount percentage correctly', function () {
        $product = Product::factory()->create([
            'price'         => 100.00,
            'compare_price' => 120.00,
        ]);

        $discountPercentage = $product->getDiscountPercentageAttribute();

        expect($discountPercentage)->toBeGreaterThan(0);
    });

    it('calculates profit margin correctly', function () {
        $product = Product::factory()->create([
            'price'      => 100.00,
            'cost_price' => 60.00,
        ]);

        expect($product->getProfitMarginAttribute())->toBe(40.0);
    });

    it('calculates markup percentage correctly', function () {
        $product = Product::factory()->create([
            'price'      => 100.00,
            'cost_price' => 60.00,
        ]);

        $markup = $product->getMarkupPercentageAttribute();
        expect($markup)->toBeGreaterThan(66.0)
            ->and($markup)->toBeLessThan(67.0);
    });

    it('handles zero cost price in profit calculations', function () {
        $product = Product::factory()->create([
            'price'      => 100.00,
            'cost_price' => 0.00,
        ]);

        expect($product->getProfitMarginAttribute())->toBe(0.0)
            ->and($product->getMarkupPercentageAttribute())->toBe(0.0);
    });

    it('calculates dimensions correctly', function () {
        $product = Product::factory()->create([
            'length' => 10.5,
            'width'  => 5.0,
            'height' => 2.5,
        ]);

        $dimensions = $product->getDimensionsAttribute();

        // Since the model casts these as decimal:2, they come back as strings
        expect($dimensions)->toBe([
            'length' => '10.50',
            'width'  => '5.00',
            'height' => '2.50',
        ]);
    });

    it('calculates volume correctly', function () {
        $product = Product::factory()->create([
            'length' => 10.0,
            'width'  => 5.0,
            'height' => 2.0,
        ]);

        expect($product->getVolumeAttribute())->toBe(100.0);
    });
});
