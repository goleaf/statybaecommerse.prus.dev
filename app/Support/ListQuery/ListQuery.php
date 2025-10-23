<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListQuery
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly int $page,
        private readonly int $perPage,
        private readonly string $sortBy,
        private readonly string $sortDirection,
        private readonly array $filters,
    ) {}

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function sortBy(): string
    {
        return $this->sortBy;
    }

    public function sortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @return array<string, mixed>
     */
    public function activeFilters(): array
    {
        return array_filter(
            $this->filters,
            static function ($value): bool {
                if ($value === null) {
                    return false;
                }

                if (is_string($value)) {
                    return $value !== '';
                }

                if (is_array($value)) {
                    return count($value) > 0;
                }

                return true;
            },
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryParameters(): array
    {
        return array_merge([
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDirection,
        ], $this->filters);
    }

    public function apply(Builder $builder, ListQueryDefinition $definition): LengthAwarePaginator
    {
        $this->applyFilters($builder, $definition);
        $this->applySorting($builder, $definition);

        $paginator = $builder->paginate($this->perPage, ['*'], 'page', $this->page);

        return $paginator->appends($this->toQueryParameters());
    }

    private function applyFilters(Builder $builder, ListQueryDefinition $definition): void
    {
        foreach ($this->activeFilters() as $name => $value) {
            $filter = $definition->getFilter($name);

            if ($filter === null) {
                continue;
            }

            $this->applyFilter($builder, $filter, $value);
        }
    }

    /**
     * @param array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string} $filter
     */
    private function applyFilter(Builder $builder, array $filter, mixed $value): void
    {
        if (isset($filter['callback']) && $filter['callback'] instanceof Closure) {
            ($filter['callback'])($builder, $value);

            return;
        }

        if (isset($filter['scope'])) {
            $builder->{$filter['scope']}($value);

            return;
        }

        $column = $filter['column'] ?? null;

        if ($column === null) {
            return;
        }

        $operator = $filter['operator'] ?? '=';

        if ($operator === 'in') {
            $builder->whereIn($column, is_array($value) ? $value : [$value]);

            return;
        }

        if ($operator === 'like') {
            $builder->where($column, 'like', '%'.$value.'%');

            return;
        }

        $builder->where($column, $operator, $value);
    }

    private function applySorting(Builder $builder, ListQueryDefinition $definition): void
    {
        $sortDefinition = $definition->getSort($this->sortBy);

        if ($sortDefinition === null) {
            $builder->orderBy($this->sortBy, $this->sortDirection);

            return;
        }

        if (isset($sortDefinition['callback']) && $sortDefinition['callback'] instanceof Closure) {
            ($sortDefinition['callback'])($builder, $this->sortDirection);

            return;
        }

        $columns = $sortDefinition['column'] ?? $this->sortBy;

        if (is_array($columns)) {
            foreach ($columns as $column) {
                $builder->orderBy($column, $this->sortDirection);
            }

            return;
        }

        $builder->orderBy($columns, $this->sortDirection);
    }
}
