<?php

declare(strict_types=1);

use App\Application\Product\DTOs\SearchProductsInputDto;
use App\Application\Product\Presenters\ProductContractPresenter;
use App\Application\Product\UseCases\SearchProductsUseCase;
use App\Domain\Product\Collections\ProductCollection;
use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Entities\ProductImage;
use App\Domain\Product\Entities\ProductVariant;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Specifications\DisplayableProductSpecification;
use Mockery as m;

afterEach(function (): void {
    m::close();
});

it('filters non displayable products and limits results', function (): void {
    $visibleProduct = new Product(
        id: 1,
        name: 'Visible Product',
        slug: 'visible-product',
        sku: 'SKU-VISIBLE',
        price: 50.0,
        salePrice: null,
        brand: ['id' => 1, 'name' => 'Brand', 'slug' => 'brand'],
        category: ['id' => 1, 'name' => 'Category', 'slug' => 'category'],
        isVisible: true,
        isFeatured: false,
        manageStock: true,
        isInStock: true,
        stockQuantity: 10,
        images: new ProductImageCollection([
            new ProductImage('https://example.com/image.jpg', 'https://example.com/thumb.jpg'),
        ]),
        variants: new ProductVariantCollection([
            new ProductVariant(1, 'Default', 'SKU-VISIBLE', 50.0, 10),
        ]),
        description: 'Test description',
        shortDescription: 'Short description',
    );

    $hiddenProduct = new Product(
        id: 2,
        name: 'Hidden Product',
        slug: 'hidden-product',
        sku: 'SKU-HIDDEN',
        price: 30.0,
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

    $repository = m::mock(ProductRepositoryInterface::class);
    $repository->shouldReceive('search')->once()->andReturn(new ProductCollection([
        $visibleProduct,
        $hiddenProduct,
    ]));

    $useCase = new SearchProductsUseCase($repository, new DisplayableProductSpecification());

    $output = $useCase->execute(new SearchProductsInputDto('visible', 10, 10));
    $payload = ProductContractPresenter::fromSearch($output);

    expect($payload['data']['items'])->toHaveCount(1)
        ->and($payload['data']['items'][0]['slug'])->toBe('visible-product')
        ->and($payload['meta']['total'])->toBe(1)
        ->and($payload['meta']['query'])->toBe('visible')
        ->and($payload['meta']['limit'])->toBe(10);
});
