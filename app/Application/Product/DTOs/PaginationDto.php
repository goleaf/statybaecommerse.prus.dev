<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

/**
 * Value object exposing pagination metadata in a transport-friendly format.
 */
final readonly class PaginationDto
{
    public function __construct(
        private int $total,
        private int $perPage,
        private int $currentPage,
    ) {
        // Constructors remain lightweight to keep the DTO immutable.
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        // Keep the last page calculation encapsulated for reuse in contract transforms.
        return (int) ceil($this->total / $this->perPage);
    }

    public function toArray(): array
    {
        // Preserve the legacy behaviour so existing call-sites continue to receive full pagination metadata.
        return $this->toContractMeta();
    }

    public function toContractData(): array
    {
        // Produce the minimal pagination payload permitted by the documented OpenAPI schema.
        return [
            'current_page' => $this->currentPage,
            'last_page'    => max(1, $this->getLastPage()),
            'per_page'     => $this->perPage,
            'total'        => $this->total,
        ];
    }

    public function toContractMeta(): array
    {
        // Extend the pagination details with range markers expected within the meta pagination snapshot.
        $hasResults = $this->total > 0;

        return array_merge(
            $this->toContractData(),
            [
                'from' => $hasResults ? ($this->perPage * ($this->currentPage - 1)) + 1 : null,
                'to'   => $hasResults ? min($this->total, $this->perPage * $this->currentPage) : null,
            ],
        );
    }
}
