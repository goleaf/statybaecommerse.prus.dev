<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Collections\ProductImageCollection;

final class ProductImageCollectionDto
{
    /** @var list<ProductImageDto> */
    private array $items;

    /**
     * @param list<ProductImageDto> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromDomainCollection(ProductImageCollection $images): self
    {
        $items = [];
        foreach ($images as $image) {
            $items[] = ProductImageDto::fromDomain($image);
        }

        return new self($items);
    }

    public function toArray(): array
    {
        return array_map(static fn (ProductImageDto $image) => $image->toArray(), $this->items);
    }
}
