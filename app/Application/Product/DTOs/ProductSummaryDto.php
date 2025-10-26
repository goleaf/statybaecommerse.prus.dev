<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\Product;

/**
 * Represents a product as exposed through the public API contract.
 */
final readonly class ProductSummaryDto
{
    public function __construct(
        private int $id,
        private string $name,
        private string $slug,
        private string $sku,
        private float $price,
        private ?float $salePrice,
        private ?array $brand,
        private ?array $category,
        private ?string $description,
        private ?string $shortDescription,
        private ProductImageCollectionDto $images,
        private ProductVariantCollectionDto $variants,
        private bool $manageStock,
        private int $stockQuantity,
        private bool $isInStock,
        private bool $isVisible,
        private bool $isFeatured,
    ) {
        // DTO stays immutable; everything is configured via promoted properties.
    }

    public static function fromDomain(Product $product): self
    {
        return new self(
            $product->getId(),
            $product->getName(),
            $product->getSlug(),
            $product->getSku(),
            $product->getPrice(),
            $product->getSalePrice(),
            $product->getBrand(),
            $product->getCategory(),
            $product->getDescription(),
            $product->getShortDescription(),
            ProductImageCollectionDto::fromDomainCollection($product->getImages()),
            ProductVariantCollectionDto::fromDomainCollection($product->getVariants()),
            $product->managesStock(),
            $product->getStockQuantity(),
            $product->isInStock(),
            $product->isVisible(),
            $product->isFeatured(),
        );
    }

    public function toContractArray(string $selfUrl): array
    {
        // Compose the contract structure required by public consumers.
        return [
            'id'                => $this->id,
            'slug'              => $this->slug,
            'name'              => $this->name,
            'sku'               => $this->sku,
            'description'       => $this->description,
            'short_description' => $this->shortDescription,
            'pricing'           => [
                'amount'      => $this->price,
                'sale_amount' => $this->salePrice,
                'currency'    => config('app.currency', 'EUR'),
            ],
            'brand'    => $this->brand,
            'category' => $this->category,
            'media'    => [
                'images' => $this->images->toArray(),
            ],
            'variants'  => $this->variants->toArray(),
            'inventory' => [
                'manage_stock'   => $this->manageStock,
                'stock_quantity' => $this->stockQuantity,
                'is_in_stock'    => $this->isInStock,
            ],
            'status' => [
                'is_visible'  => $this->isVisible,
                'is_featured' => $this->isFeatured,
            ],
            'links' => [
                'self' => $selfUrl,
            ],
        ];
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
