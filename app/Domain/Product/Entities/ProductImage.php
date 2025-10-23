<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

final class ProductImage
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $thumbnailUrl = null,
        private readonly ?string $altText = null,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }
}
