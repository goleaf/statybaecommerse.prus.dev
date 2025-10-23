<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

/**
 * DTO describing the results of a catalog listing call.
 */
final readonly class ListCatalogProductsOutputDto
{
    public function __construct(
        private ProductSummaryCollectionDto $products,
        private PaginationDto $pagination,
    ) {
        // Intentionally left blank; promoted properties hold the payload.
    }

    public function getProducts(): ProductSummaryCollectionDto
    {
        return $this->products;
    }

    public function getPagination(): PaginationDto
    {
        return $this->pagination;
    }
}
