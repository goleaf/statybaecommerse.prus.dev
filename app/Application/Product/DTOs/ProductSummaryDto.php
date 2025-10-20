<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\Product;

final class ProductSummaryDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $sku,
        private readonly float $price,
        private readonly ?float $salePrice,
        private readonly ?string $brand,
        private readonly ?string $category,
        private readonly ?string $image,
        private readonly ?string $thumbnail,
        private readonly int $stockQuantity,
    ) {}

    public static function fromDomain(Product $product): self
    {
        $primaryImage = $product->getImages()->first();

        return new self(
            $product->getId(),
            $product->getName(),
            $product->getSlug(),
            $product->getSku(),
            $product->getPrice(),
            $product->getSalePrice(),
            $product->getBrandName(),
            $product->getCategoryName(),
            $primaryImage?->getUrl(),
            $primaryImage?->getThumbnailUrl(),
            $product->getStockQuantity(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => $this->price,
            'sale_price' => $this->salePrice,
            'brand' => $this->brand,
            'category' => $this->category,
            'image' => $this->image,
            'thumb' => $this->thumbnail,
            'stock_quantity' => $this->stockQuantity,
        ];
    }
}
