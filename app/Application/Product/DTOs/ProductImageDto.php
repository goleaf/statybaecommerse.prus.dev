<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\ProductImage;

final class ProductImageDto
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $thumbnailUrl,
        private readonly ?string $altText,
    ) {}

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
            'thumb' => $this->thumbnailUrl,
            'alt' => $this->altText,
        ];
    }
}
