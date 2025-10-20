<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class SearchProductsOutputDto
{
    public function __construct(
        private readonly ProductSummaryCollectionDto $products,
        private readonly string $query,
        private readonly int $total,
        private readonly int $limit,
    ) {}

    public function toArray(): array
    {
        return [
            'products' => $this->products->toArray(),
            'query' => $this->query,
            'total' => $this->total,
            'limit' => $this->limit,
        ];
    }
}
