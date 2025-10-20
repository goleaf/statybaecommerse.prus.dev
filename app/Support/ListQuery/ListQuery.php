<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Database\Eloquent\Builder;

final class ListQuery
{
    /**
     * @param array<string,mixed> $filters
     */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $sortField,
        public readonly string $sortDirection,
        public readonly array $filters,
    ) {
    }

    public function apply(Builder $builder, ListQueryDefinition $definition): Builder
    {
        return $builder->orderBy(
            $definition->resolveSortColumn($this->sortField),
            $this->sortDirection,
        );
    }

    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters) && $this->filters[$key] !== null && $this->filters[$key] !== '';
    }

    public function filter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }
}
