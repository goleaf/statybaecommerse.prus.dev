<?php

declare(strict_types=1);

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Entities\ProductImage;
use App\Domain\Product\Entities\ProductVariant;

it('unit: computes sale state and effective price correctly', function (): void {
    // Build a product with a qualifying sale price.
    $product = new Product(
        id: 1,
        name: 'Sale Product',
        slug: 'sale-product',
        sku: 'SKU-001',
        price: 120.0,
        salePrice: 90.0,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: false,
        isInStock: true,
        stockQuantity: 5,
        images: new ProductImageCollection,
        variants: new ProductVariantCollection,
    );

    expect($product->hasActiveSale())->toBeTrue()
        ->and($product->getEffectivePrice())->toBe(90.0);

    // A higher sale price should be ignored in favour of the standard price.
    $fullPriceProduct = new Product(
        id: 2,
        name: 'Full Price',
        slug: 'full-price',
        sku: 'SKU-002',
        price: 80.0,
        salePrice: 90.0,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: false,
        isInStock: true,
        stockQuantity: 5,
        images: new ProductImageCollection,
        variants: new ProductVariantCollection,
    );

    expect($fullPriceProduct->hasActiveSale())->toBeFalse()
        ->and($fullPriceProduct->getEffectivePrice())->toBe(80.0);
});

it('unit: determines availability based on visibility, pricing, and stock', function (): void {
    // Start with a valid, purchasable product record.
    $available = new Product(
        id: 3,
        name: 'Available',
        slug: 'available',
        sku: 'SKU-003',
        price: 50.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: true,
        isInStock: true,
        stockQuantity: 2,
        images: new ProductImageCollection,
        variants: new ProductVariantCollection,
    );

    expect($available->isAvailableForPurchase())->toBeTrue();

    // Hidden products should be rejected regardless of inventory or price.
    $hidden = new Product(
        id: 4,
        name: 'Hidden',
        slug: 'hidden',
        sku: 'SKU-004',
        price: 50.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: false,
        isFeatured: false,
        manageStock: true,
        isInStock: true,
        stockQuantity: 2,
        images: new ProductImageCollection,
        variants: new ProductVariantCollection,
    );

    expect($hidden->isAvailableForPurchase())->toBeFalse();

    // Managed inventory without stock should also be rejected.
    $outOfStock = new Product(
        id: 5,
        name: 'Empty',
        slug: 'empty',
        sku: 'SKU-005',
        price: 50.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: true,
        isInStock: false,
        stockQuantity: 0,
        images: new ProductImageCollection,
        variants: new ProductVariantCollection,
    );

    expect($outOfStock->isAvailableForPurchase())->toBeFalse();
});

it('unit: returns the primary image when available', function (): void {
    // Include both primary and secondary images to verify ordering.
    $images = new ProductImageCollection([
        new ProductImage('https://example.com/primary.jpg', 'https://example.com/primary-thumb.jpg', 'Primary'),
        new ProductImage('https://example.com/secondary.jpg', 'https://example.com/secondary-thumb.jpg', 'Secondary'),
    ]);

    $product = new Product(
        id: 6,
        name: 'Gallery Product',
        slug: 'gallery-product',
        sku: 'SKU-006',
        price: 40.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: false,
        isInStock: true,
        stockQuantity: 1,
        images: $images,
        variants: new ProductVariantCollection([
            new ProductVariant(1, 'Default', 'SKU-006', 40.0, null),
        ]),
    );

    expect($product->getPrimaryImage())->toBeInstanceOf(ProductImage::class)
        ->and($product->getPrimaryImage()?->getUrl())->toBe('https://example.com/primary.jpg');
});
