<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\ProductVariant;

/**
 * DTO describing a single product variant.
 */
final class ProductVariantDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?int $stockQuantity,
    ) {
        // All state is captured via constructor promotion for clarity.
    }

    public static function fromDomain(ProductVariant $variant): self
    {
        return new self(
            $variant->getId(),
            $variant->getName(),
            $variant->getSku(),
            $variant->getPrice(),
            $variant->getStockQuantity(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock_quantity' => $this->stockQuantity,
        ];
    }
}
