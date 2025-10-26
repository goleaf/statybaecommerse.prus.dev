<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Collections\ProductImageCollection;

/**
 * Lightweight collection DTO for serialising product images.
 */
final readonly class ProductImageCollectionDto
{
    /**
     * @param list<ProductImageDto> $items
     */
    public function __construct(private array $items) {}

    public static function fromDomainCollection(ProductImageCollection $images): self
    {
        $items = [];
        foreach ($images as $image) {
            $items[] = ProductImageDto::fromDomain($image);
        }

        return new self($items);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (ProductImageDto $image): array => $image->toArray(), $this->items);
    }
}
