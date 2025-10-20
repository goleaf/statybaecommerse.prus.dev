<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

final class ProductVariant
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?int $stockQuantity = null,
    ) {}

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
