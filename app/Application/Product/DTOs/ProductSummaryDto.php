<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\Product;

/**
 * Represents a product as exposed through the public API contract.
 */
final class ProductSummaryDto
{
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
        private readonly ?string $description,
        private readonly ?string $shortDescription,
        private readonly ProductImageCollectionDto $images,
        private readonly ProductVariantCollectionDto $variants,
        private readonly bool $manageStock,
        private readonly int $stockQuantity,
        private readonly bool $isInStock,
        private readonly bool $isVisible,
        private readonly bool $isFeatured,
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
            $product->getCategories(),
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
            // Keep the public contract ergonomic by exposing a simple brand name string.
            'brand'      => $this->brand['name'] ?? null,
            'categories' => array_map(
                // Surface category labels while preserving their order from the repository.
                static fn (array $category): string => $category['name'],
                $this->categories,
            ),
            'media' => [
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
