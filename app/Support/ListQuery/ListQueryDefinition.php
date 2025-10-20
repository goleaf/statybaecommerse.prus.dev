<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Closure;
use InvalidArgumentException;

final class ListQueryDefinition
{
    private int $defaultPerPage = 15;

    private int $maxPerPage = 100;

    private string $defaultSortBy = 'id';

    private string $defaultSortDirection = 'asc';

    /**
     * @var array<string, array{column?: string|array<int, string>, callback?: Closure}>
     */
    private array $sorts = [];

    /**
     * @var array<string, array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string}>
     */
    private array $filters = [];

    public static function make(): self
    {
        return new self();
    }

    public function defaultPerPage(int $perPage): self
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('The default per-page value must be at least 1.');
        }

        $this->defaultPerPage = $perPage;

        return $this;
    }

    public function maxPerPage(int $perPage): self
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('The maximum per-page value must be at least 1.');
        }

        $this->maxPerPage = $perPage;

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('The default sort direction must be either asc or desc.');
        }

        $this->defaultSortBy = $column;
        $this->defaultSortDirection = $direction;

        if (! array_key_exists($column, $this->sorts)) {
            $this->addSort($column, ['column' => $column]);
        }

        return $this;
    }

    /**
     * @param array<string, array{column?: string|array<int, string>, callback?: Closure}|string> $sorts
     */
    public function allowedSorts(array $sorts): self
    {
        foreach ($sorts as $name => $definition) {
            $this->addSort($name, $definition);
        }

        return $this;
    }

    /**
     * @param array{column?: string|array<int, string>, callback?: Closure}|string $definition
     */
    public function addSort(string $name, array|string $definition): self
    {
        if (is_string($definition)) {
            $definition = ['column' => $definition];
        }

        if (! isset($definition['column']) && ! isset($definition['callback'])) {
            $definition['column'] = $name;
        }

        $this->sorts[$name] = $definition;

        return $this;
    }

    /**
     * @param array<string, array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string}> $filters
     */
    public function filters(array $filters): self
    {
        foreach ($filters as $name => $definition) {
            $this->addFilter($name, $definition);
        }

        return $this;
    }

    /**
     * @param array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string} $definition
     */
    public function addFilter(string $name, array $definition): self
    {
        $this->filters[$name] = array_merge([
            'column' => $name,
            'type' => 'string',
            'operator' => '=',
            'callback' => null,
            'scope' => null,
            'nullable' => false,
            'allowed' => null,
            'enum' => null,
        ], $definition);

        return $this;
    }

    public function getDefaultPerPage(): int
    {
        return $this->defaultPerPage;
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    public function getDefaultSortBy(): string
    {
        return $this->defaultSortBy;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }

    /**
     * @return array<string, array{column?: string|array<int, string>, callback?: Closure}>
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @return array<string, array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string}>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array{column?: string|array<int, string>, callback?: Closure}|null
     */
    public function getSort(string $name): ?array
    {
        return $this->sorts[$name] ?? null;
    }

    /**
     * @return array{column?: string, type?: string, operator?: string, callback?: Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string}|null
     */
    public function getFilter(string $name): ?array
    {
        return $this->filters[$name] ?? null;
    }
}
