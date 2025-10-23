<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\ListCatalogProductsInputDto;
use App\Application\Product\DTOs\ListCatalogProductsOutputDto;
use App\Application\Product\DTOs\PaginationDto;
use App\Application\Product\DTOs\ProductSummaryCollectionDto;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Specifications\DisplayableProductSpecification;
use App\Domain\Product\ValueObjects\ProductCatalogQuery;

/**
 * Coordinates the catalog listing flow including filtering and pagination.
 */
final class ListCatalogProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly DisplayableProductSpecification $displayableProductSpecification,
    ) {
        // Dependencies allow reuse of the specification in tests.
    }

    public function execute(ListCatalogProductsInputDto $input): ListCatalogProductsOutputDto
    {
        $query = new ProductCatalogQuery(
            $input->getPerPage(),
            $input->getCategorySlug(),
            $input->getBrandSlug(),
            $input->getSortBy(),
            $input->getSortOrder(),
        );

        $products = $this->repository->getCatalogProducts($query)
            ->filter(fn (Product $product) => $this->displayableProductSpecification->isSatisfiedBy($product));

        $total = $products->count();
        $offset = ($input->getPage() - 1) * $input->getPerPage();
        $paginatedProducts = $products->slice($offset, $input->getPerPage());

        return new ListCatalogProductsOutputDto(
            ProductSummaryCollectionDto::fromDomainCollection($paginatedProducts),
            new PaginationDto($total, $input->getPerPage(), $input->getPage()),
        );
    }
}
