<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

/**
 * Value object exposing pagination metadata in a transport-friendly format.
 */
final class PaginationDto
{
    public function __construct(
        private readonly int $total,
        private readonly int $perPage,
        private readonly int $currentPage,
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
        return (int) ceil($this->total / $this->perPage);
    }

    public function toArray(): array
    {
        // Provide the structure expected by our public contract schema.
        return [
            'current_page' => $this->currentPage,
            'last_page' => max(1, $this->getLastPage()),
            'per_page' => $this->perPage,
            'total' => $this->total,
            'from' => $this->total === 0 ? 0 : ($this->perPage * ($this->currentPage - 1)) + 1,
            'to' => $this->total === 0 ? 0 : min($this->total, $this->perPage * $this->currentPage),
        ];
    }
}
