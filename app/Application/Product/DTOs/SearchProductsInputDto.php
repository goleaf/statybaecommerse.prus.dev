<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use InvalidArgumentException;

/**
 * Input DTO for keyword-based product search.
 */
final readonly class SearchProductsInputDto
{
    public function __construct(
        private string $query,
        private int $limit,
        private int $timeoutSeconds,
    ) {
        if ($this->limit < 1) {
            throw new InvalidArgumentException('Search limit must be at least 1.');
        }
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }
}
