<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class PaginationDto
{
    public function __construct(
        private readonly int $total,
        private readonly int $perPage,
        private readonly int $currentPage,
    ) {}

    public function getLastPage(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page' => $this->getLastPage(),
            'per_page' => $this->perPage,
            'total' => $this->total,
            'from' => $this->total === 0 ? 0 : ($this->perPage * ($this->currentPage - 1)) + 1,
            'to' => $this->total === 0 ? 0 : min($this->total, $this->perPage * $this->currentPage),
        ];
    }
}
