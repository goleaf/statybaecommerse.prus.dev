<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

final class ListQueryDefinition
{
    /**
     * @param array<string,string> $allowedSorts
     */
    public function __construct(
        public readonly array $allowedSorts,
        public readonly string $defaultSort,
        public readonly string $defaultDirection = 'asc',
        public readonly int $defaultPerPage = 15,
        public readonly int $maxPerPage = 100,
    ) {
    }

    /**
     * @param array<string,string> $allowedSorts
     */
    public static function make(
        array $allowedSorts,
        string $defaultSort,
        string $defaultDirection = 'asc',
        int $defaultPerPage = 15,
        int $maxPerPage = 100,
    ): self {
        return new self(
            $allowedSorts,
            $defaultSort,
            $defaultDirection,
            $defaultPerPage,
            $maxPerPage,
        );
    }

    public function resolveSortColumn(string $field): string
    {
        return $this->allowedSorts[$field] ?? $field;
    }
}
