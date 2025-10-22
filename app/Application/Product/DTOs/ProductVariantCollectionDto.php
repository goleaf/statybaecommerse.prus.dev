<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Collections\ProductVariantCollection;

/**
 * Helper DTO ensuring product variants are serialised consistently.
 */
final class ProductVariantCollectionDto
{
    /** @var list<ProductVariantDto> */
    private array $items;

    /**
     * @param list<ProductVariantDto> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromDomainCollection(ProductVariantCollection $variants): self
    {
        $items = [];
        foreach ($variants as $variant) {
            $items[] = ProductVariantDto::fromDomain($variant);
        }

        return new self($items);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (ProductVariantDto $variant) => $variant->toArray(), $this->items);
    }
}
