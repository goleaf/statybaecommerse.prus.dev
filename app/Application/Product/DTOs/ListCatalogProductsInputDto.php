<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class ListCatalogProductsInputDto
{
    public function __construct(
        private readonly int $perPage,
        private readonly int $page,
        private readonly ?string $categorySlug,
        private readonly ?string $brandSlug,
        private readonly string $sortBy,
        private readonly string $sortOrder,
    ) {}

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
