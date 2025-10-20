<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\Product;

final class ProductDetailsDto
{
    public function __construct(
        private readonly ProductSummaryDto $summary,
        private readonly ?string $description,
        private readonly ProductImageCollectionDto $images,
        private readonly ProductVariantCollectionDto $variants,
    ) {}

    public static function fromDomain(Product $product): self
    {
        return new self(
            ProductSummaryDto::fromDomain($product),
            $product->getDescription(),
            ProductImageCollectionDto::fromDomainCollection($product->getImages()),
            ProductVariantCollectionDto::fromDomainCollection($product->getVariants()),
        );
    }

    public function toArray(): array
    {
        $summary = $this->summary->toArray();
        $summary['description'] = $this->description;
        $summary['images'] = $this->images->toArray();
        $summary['variants'] = $this->variants->toArray();

        return ['product' => $summary];
    }
}
