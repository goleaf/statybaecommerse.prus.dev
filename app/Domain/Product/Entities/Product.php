<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;

/**
 * Rich domain representation of a product tailored for read models.
 */
final class Product
{
    /**
     * @param array{id:int,name:string,slug:string}|null  $brand
     * @param array{id:int,name:string,slug:string}|null  $category
     * @param list<array{id:int,name:string,slug:string}> $categories
     */
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?float $salePrice,
        private readonly ?array $brand,
        private readonly ?array $category,
        private readonly array $categories,
        private readonly bool $isVisible,
        private readonly bool $isFeatured,
        private readonly bool $manageStock,
        private readonly bool $isInStock,
        private readonly int $stockQuantity,
        private readonly ProductImageCollection $images,
        private readonly ProductVariantCollection $variants,
        private readonly ?string $description = null,
        private readonly ?string $shortDescription = null,
    ) {
        // Domain entity remains immutable once instantiated.
    }

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

    /**
     * @return array{id:int,name:string,slug:string}|null
     */
    public function getBrand(): ?array
    {
        return $this->brand;
    }

    public function getBrandName(): ?string
    {
        return $this->brand['name'] ?? null;
    }

    /**
     * @return array{id:int,name:string,slug:string}|null
     */
    public function getCategory(): ?array
    {
        return $this->category;
    }

    /**
     * @return list<array{id:int,name:string,slug:string}>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getCategoryName(): ?string
    {
        return $this->category['name'] ?? null;
    }

    /**
     * @return list<string>
     */
    public function getCategoryNames(): array
    {
        return array_map(
            // Extract the human readable title for each attached category.
            static fn (array $category): string => $category['name'],
            $this->categories,
        );
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function managesStock(): bool
    {
        return $this->manageStock;
    }

    public function isInStock(): bool
    {
        return $this->isInStock;
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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }
}
