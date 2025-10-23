<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use InvalidArgumentException;

/**
 * @phpstan-type FilterConfig array{
 *     type?: string,
 *     column?: string|null,
 *     operator?: string,
 *     nullable?: bool,
 *     allowed?: array<int|string, mixed>,
 *     callback?: callable|null,
 *     allow_empty?: bool,
 * }
 * @phpstan-type SortConfig array{
 *     column: string,
 *     default_direction?: string,
 * }
 */
final class ListQueryDefinition
{
    /**
     * @param  array<string, FilterConfig>  $filters
     * @param  array<string, string|SortConfig>  $sortable
     */
    public function __construct(
        private readonly array $filters = [],
        private readonly array $sortable = [],
        private readonly ?string $defaultSort = null,
        private readonly string $defaultDirection = 'asc',
        private readonly int $defaultPerPage = 15,
        private readonly int $maxPerPage = 100,
        private readonly int $minPerPage = 1,
    ) {
        if ($this->minPerPage < 1) {
            throw new InvalidArgumentException('Minimum per page must be at least 1.');
        }

        if ($this->maxPerPage < $this->minPerPage) {
            throw new InvalidArgumentException('Maximum per page must be greater than or equal to the minimum per page value.');
        }
    }

    /**
     * @return array<string, FilterConfig>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @return array<string, SortConfig>
     */
    public function sortable(): array
    {
        $normalised = [];

        foreach ($this->sortable as $key => $config) {
            if (is_string($config)) {
                $normalised[$key] = [
                    'column' => $config,
                ];

                continue;
            }

            $normalised[$key] = [
                'column' => $config['column'],
                'default_direction' => $config['default_direction'] ?? null,
            ];
        }

        return $normalised;
    }

    public function defaultSort(): ?string
    {
        return $this->defaultSort;
    }

    public function defaultDirection(): string
    {
        return strtolower($this->defaultDirection) === 'desc' ? 'desc' : 'asc';
    }

    public function defaultPerPage(): int
    {
        return $this->defaultPerPage;
    }

    public function maxPerPage(): int
    {
        return $this->maxPerPage;
    }

    public function minPerPage(): int
    {
        return $this->minPerPage;
    }
}
