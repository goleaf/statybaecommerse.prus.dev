<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\ProductSummaryCollectionDto;
use App\Application\Product\DTOs\SearchProductsInputDto;
use App\Application\Product\DTOs\SearchProductsOutputDto;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Specifications\DisplayableProductSpecification;
use App\Domain\Product\ValueObjects\ProductSearchCriteria;

/**
 * Performs keyword search over products with a displayable filter.
 */
final class SearchProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly DisplayableProductSpecification $displayableProductSpecification,
    ) {
        // Both dependencies are simple to mock in tests.
    }

    public function execute(SearchProductsInputDto $input): SearchProductsOutputDto
    {
        $criteria = new ProductSearchCriteria(
            $input->getQuery(),
            $input->getLimit(),
            $input->getTimeoutSeconds(),
        );

        $products = $this->repository->search($criteria)
            ->filter(fn (Product $product) => $this->displayableProductSpecification->isSatisfiedBy($product))
            ->slice(0, $input->getLimit());

        $collectionDto = ProductSummaryCollectionDto::fromDomainCollection($products);

        return new SearchProductsOutputDto(
            $collectionDto,
            $input->getQuery(),
            $collectionDto->count(),
            $input->getLimit(),
        );
    }
}
