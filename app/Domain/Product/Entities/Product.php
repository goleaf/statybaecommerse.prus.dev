<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use Illuminate\Support\Arr;

/**
 * Rich domain representation of a product tailored for read models.
 */
final class Product
{
    /**
     * @param array{id:int,name:string,slug:string}|null $brand
     * @param array{id:int,name:string,slug:string}|null $category
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
        return Arr::get($this->brand, 'name');
    }

    /**
     * @return string|null Brand slug if present, otherwise null for anonymous brands.
     */
    public function getBrandSlug(): ?string
    {
        return Arr::get($this->brand, 'slug');
    }

    /**
     * @return array{id:int,name:string,slug:string}|null
     */
    public function getCategory(): ?array
    {
        return $this->category;
    }

    public function getCategoryName(): ?string
    {
        return Arr::get($this->category, 'name');
    }

    /**
     * @return string|null Category slug when a primary category exists.
     */
    public function getCategorySlug(): ?string
    {
        return Arr::get($this->category, 'slug');
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

    /**
     * Determine if the product currently has a valid sale price applied.
     */
    public function hasActiveSale(): bool
    {
        if ($this->salePrice === null) {
            return false;
        }

        if ($this->salePrice <= 0.0) {
            return false;
        }

        return $this->salePrice < $this->price;
    }

    /**
     * Provide the price that should be shown to customers, preferring sale amounts.
     */
    public function getEffectivePrice(): float
    {
        return $this->hasActiveSale() ? $this->salePrice : $this->price;
    }

    /**
     * Check aggregate state to decide if the product can be listed for purchase.
     */
    public function isAvailableForPurchase(): bool
    {
        if (! $this->isVisible()) {
            return false;
        }

        if ($this->getEffectivePrice() <= 0.0) {
            return false;
        }

        if ($this->name === '' || $this->slug === '') {
            return false;
        }

        if ($this->managesStock() && ! $this->isInStock()) {
            return false;
        }

        return true;
    }

    /**
     * Surface the leading image which acts as the default thumbnail for listings.
     */
    public function getPrimaryImage(): ?ProductImage
    {
        return $this->images->first();
    }
}
