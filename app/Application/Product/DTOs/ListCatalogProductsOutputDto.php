<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class ListCatalogProductsOutputDto
{
    public function __construct(
        private readonly ProductSummaryCollectionDto $products,
        private readonly PaginationDto $pagination,
    ) {}

    public function toArray(): array
    {
        return [
            'products' => $this->products->toArray(),
            'pagination' => $this->pagination->toArray(),
        ];
    }
}
