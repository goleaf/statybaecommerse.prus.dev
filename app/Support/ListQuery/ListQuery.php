<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Database\Eloquent\Builder;

/**
 * @phpstan-type AppliedFilter array{
 *     key: string,
 *     value: mixed,
 *     column: string|null,
 *     operator: string,
 *     callback: (callable(Builder, mixed): void)|null,
 * }
 * @phpstan-type AppliedSort array{
 *     key: string,
 *     column: string,
 *     direction: string,
 * }
 */
final class ListQuery
{
    /**
     * @param  array<int, AppliedFilter>  $filterDefinitions
     * @param  array<int, AppliedSort>  $sortDefinitions
     * @param  array<string, mixed>  $filters
     * @param  array<int, array{key: string, direction: string}>  $sorts
     */
    public function __construct(
        private readonly int $page,
        private readonly int $perPage,
        private readonly array $filterDefinitions,
        private readonly array $sortDefinitions,
        private readonly array $filters,
        private readonly array $sorts,
    ) {}

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Apply the configured filters to the query builder.
     */
    public function applyFilters(Builder $builder): Builder
    {
        foreach ($this->filterDefinitions as $filter) {
            if ($filter['callback'] !== null) {
                ($filter['callback'])($builder, $filter['value']);

                continue;
            }

            if ($filter['column'] === null) {
                continue;
            }

            $builder->where($filter['column'], $filter['operator'], $filter['value']);
        }

        return $builder;
    }

    /**
     * Apply the configured sorts to the query builder.
     */
    public function applySorts(Builder $builder): Builder
    {
        foreach ($this->sortDefinitions as $sort) {
            $builder->orderBy($sort['column'], $sort['direction']);
        }

        return $builder;
    }

    /**
     * Apply both filters and sorts to the query builder.
     */
    public function apply(Builder $builder): Builder
    {
        return $this->applySorts($this->applyFilters($builder));
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @return array<int, array{key: string, direction: string}>
     */
    public function sorts(): array
    {
        return $this->sorts;
    }

    public function hasSort(string $key): bool
    {
        foreach ($this->sorts as $sort) {
            if ($sort['key'] === $key) {
                return true;
            }
        }

        return false;
    }

    public function filterValue(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }
}
