<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class SearchProductsInputDto
{
    public function __construct(
        private readonly string $query,
        private readonly int $limit,
        private readonly int $timeoutSeconds,
    ) {}

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
