<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;

final class Product
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?float $salePrice,
        private readonly ?string $brandName,
        private readonly ?string $categoryName,
        private readonly bool $isVisible,
        private readonly int $stockQuantity,
        private readonly ProductImageCollection $images,
        private readonly ProductVariantCollection $variants,
        private readonly ?string $description = null,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSalePrice(): ?float
    {
        return $this->salePrice;
    }

    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    public function getImages(): ProductImageCollection
    {
        return $this->images;
    }

    public function getVariants(): ProductVariantCollection
    {
        return $this->variants;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
