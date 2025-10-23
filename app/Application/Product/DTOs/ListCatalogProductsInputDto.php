<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

/**
 * Immutable DTO capturing filters and pagination for the catalog listing.
 */
final readonly class ListCatalogProductsInputDto
{
    public function __construct(
        private int $perPage,
        private int $page,
        private ?string $categorySlug,
        private ?string $brandSlug,
        private string $sortBy,
        private string $sortOrder,
    ) {
        // Normalise pagination to always be within a sensible range.
        if ($this->perPage < 1) {
            throw new \InvalidArgumentException('Per-page value must be greater than zero.');
        }
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page value must be greater than zero.');
        }
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPage(): int
    {
        return $this->page;
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
