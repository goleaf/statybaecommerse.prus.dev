<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

/**
 * Read-only representation of a variant attached to a product.
 */
final class ProductVariant
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?int $stockQuantity = null,
    ) {
        // Immutable variant details for predictable transformations.
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }
}
