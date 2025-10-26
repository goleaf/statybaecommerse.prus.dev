<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\ProductImage;

/**
 * DTO representing a single product image for API responses.
 */
final readonly class ProductImageDto
{
    public function __construct(
        private string $url,
        private ?string $thumbnailUrl,
        private ?string $altText,
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
            'url'       => $this->url,
            'thumbnail' => $this->thumbnailUrl,
            'alt'       => $this->altText,
        ];
    }
}
