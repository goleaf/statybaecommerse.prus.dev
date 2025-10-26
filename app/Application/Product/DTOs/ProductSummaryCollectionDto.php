<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Collections\ProductCollection;

/**
 * Thin wrapper ensuring we work with a consistent list of product summary DTOs.
 */
final readonly class ProductSummaryCollectionDto
{
    /**
     * @param list<ProductSummaryDto> $items
     */
    public function __construct(private array $items) {}

    public static function fromDomainCollection(ProductCollection $products): self
    {
        $items = [];
        foreach ($products as $product) {
            $items[] = ProductSummaryDto::fromDomain($product);
        }

        return new self($items);
    }

    public function count(): int
    {
        return is_countable($this->items) ? count($this->items) : 0;
    }

    /**
     * @return list<ProductSummaryDto>
     */
    public function all(): array
    {
        return $this->items;
    }
}
