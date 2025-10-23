<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\ProductImage;

/**
 * DTO representing a single product image for API responses.
 */
final class ProductImageDto
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $thumbnailUrl,
        private readonly ?string $altText,
    ) {
        // Constructor assignment keeps the DTO immutable and predictable.
    }

    public static function fromDomain(ProductImage $image): self
    {
        return new self(
            $image->getUrl(),
            $image->getThumbnailUrl(),
            $image->getAltText(),
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'thumbnail' => $this->thumbnailUrl,
            'alt' => $this->altText,
        ];
    }
}
