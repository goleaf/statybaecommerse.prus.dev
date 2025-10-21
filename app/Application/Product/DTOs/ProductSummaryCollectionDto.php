<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Collections\ProductCollection;

/**
 * Thin wrapper ensuring we work with a consistent list of product summary DTOs.
 */
final class ProductSummaryCollectionDto
{
    /** @var list<ProductSummaryDto> */
    private array $items;

    /**
     * @param list<ProductSummaryDto> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

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
        return count($this->items);
    }

    /**
     * @return list<ProductSummaryDto>
     */
    public function all(): array
    {
        return $this->items;
    }
}
