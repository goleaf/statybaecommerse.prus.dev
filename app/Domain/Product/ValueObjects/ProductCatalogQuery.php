<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

/**
 * Encapsulates pagination and sorting inputs for catalog listings.
 */
final class ProductCatalogQuery
{
    public function __construct(
        private readonly int $perPage,
        private readonly ?string $categorySlug,
        private readonly ?string $brandSlug,
        private readonly string $sortBy,
        private readonly string $sortOrder,
    ) {
        if ($this->perPage < 1) {
            throw new \InvalidArgumentException('Per-page value must be positive.');
        }
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCategorySlug(): ?string
    {
        return $this->categorySlug;
    }

    public function getBrandSlug(): ?string
    {
        return $this->brandSlug;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }
}
