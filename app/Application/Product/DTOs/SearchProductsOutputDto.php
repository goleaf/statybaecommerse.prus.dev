<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

/**
 * DTO representing a product search result set.
 */
final class SearchProductsOutputDto
{
    public function __construct(
        private readonly ProductSummaryCollectionDto $products,
        private readonly string $query,
        private readonly int $total,
        private readonly int $limit,
    ) {
        // No additional work is needed in the constructor.
    }

    public function getProducts(): ProductSummaryCollectionDto
    {
        return $this->products;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
