<?php

declare(strict_types=1);

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Entities\ProductImage;
use App\Domain\Product\Entities\ProductVariant;
use App\Domain\Product\Specifications\DisplayableProductSpecification;

it('unit: accepts visible products with price and slug', function (): void {
    $product = new Product(
        id: 1,
        name: 'Test Product',
        slug: 'test-product',
        sku: 'SKU-001',
        price: 100.0,
        salePrice: null,
        brand: ['id' => 1, 'name' => 'Brand', 'slug' => 'brand'],
        category: ['id' => 1, 'name' => 'Category', 'slug' => 'category'],
        isVisible: true,
        isFeatured: true,
        manageStock: true,
        isInStock: true,
        stockQuantity: 5,
        images: new ProductImageCollection([
            new ProductImage('https://example.com/image.jpg', 'https://example.com/thumb.jpg'),
        ]),
        variants: new ProductVariantCollection([
            new ProductVariant(1, 'Default', 'SKU-001', 100.0, 5),
        ]),
        description: 'Description',
        shortDescription: 'Short description',
    );

    $specification = new DisplayableProductSpecification();

    expect($specification->isSatisfiedBy($product))->toBeTrue();
});

it('unit: rejects hidden or non priced products', function (): void {
    $hidden = new Product(
        id: 2,
        name: 'Hidden Product',
        slug: 'hidden-product',
        sku: 'SKU-002',
        price: 100.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: false,
        isFeatured: false,
        manageStock: true,
        isInStock: false,
        stockQuantity: 0,
        images: new ProductImageCollection(),
        variants: new ProductVariantCollection(),
        description: null,
        shortDescription: null,
    );

    $free = new Product(
        id: 3,
        name: 'Free Product',
        slug: 'free-product',
        sku: 'SKU-003',
        price: 0.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: true,
        isInStock: false,
        stockQuantity: 0,
        images: new ProductImageCollection(),
        variants: new ProductVariantCollection(),
        description: null,
        shortDescription: null,
    );

    $nameless = new Product(
        id: 4,
        name: '',
        slug: '',
        sku: 'SKU-004',
        price: 10.0,
        salePrice: null,
        brand: null,
        category: null,
        isVisible: true,
        isFeatured: false,
        manageStock: true,
        isInStock: true,
        stockQuantity: 0,
        images: new ProductImageCollection(),
        variants: new ProductVariantCollection(),
        description: null,
        shortDescription: null,
    );

    $specification = new DisplayableProductSpecification();

    expect($specification->isSatisfiedBy($hidden))->toBeFalse()
        ->and($specification->isSatisfiedBy($free))->toBeFalse()
        ->and($specification->isSatisfiedBy($nameless))->toBeFalse();
});
