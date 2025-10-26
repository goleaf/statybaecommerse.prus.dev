<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Arr;
use InvalidArgumentException;

final class SearchQueryData
{
    public const DEFAULT_PER_PAGE = 10;

    public const MAX_PER_PAGE = 50;

    public const ALLOWED_SORTS = ['relevance', 'newest', 'price_asc', 'price_desc'];

    private readonly string $query;

    private readonly int $page;

    private readonly int $perPage;

    /**
     * @var array<int, string>
     */
    private readonly array $types;

    /**
     * @var array<string, mixed>
     */
    private readonly array $filters;

    private readonly string $sort;

    /**
     * @var array<string, mixed>
     */
    private readonly array $context;

    /**
     * @param array<int, string>   $types
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $context
     */
    private function __construct(string $query, int $page, int $perPage, array $types, array $filters, string $sort, array $context)
    {
        $this->query = $query;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->types = $types;
        $this->filters = $filters;
        $this->sort = $sort;
        $this->context = $context;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $context
     */
    public static function fromArray(array $input, array $context = []): self
    {
        $rawQueryValue = $input['query'] ?? $input['q'] ?? '';
        if (! is_scalar($rawQueryValue)) {
            $rawQueryValue = '';
        }

        $rawQuery = (string) $rawQueryValue;
        $query = trim(preg_replace('/\s+/', ' ', $rawQuery) ?? '');

        if ($query === '') {
            throw new InvalidArgumentException('Search query must be provided.');
        }

        $pageValue = $input['page'] ?? 1;
        if (! is_numeric($pageValue)) {
            $pageValue = 1;
        }

        $page = (int) $pageValue;
        if ($page < 1) {
            $page = 1;
        }

        $perPageValue = $input['per_page'] ?? self::DEFAULT_PER_PAGE;
        if (! is_numeric($perPageValue)) {
            $perPageValue = self::DEFAULT_PER_PAGE;
        }

        $perPage = (int) $perPageValue;
        if ($perPage < 1) {
            $perPage = 1;
        }

        $perPage = min($perPage, self::MAX_PER_PAGE);

        $rawTypes = $input['types'] ?? ['product', 'category', 'brand'];
        if (! is_array($rawTypes)) {
            $rawTypes = [$rawTypes];
        }

        $allowedTypes = ['product', 'category', 'brand'];
        $types = [];

        foreach ($rawTypes as $value) {
            if (! is_string($value)) {
                continue;
            }

            $normalized = mb_strtolower(trim($value));

            // Guard against upstream clients sending empty or unsupported type identifiers (bugfix: normalize case-sensitive values).
            if ($normalized === '' || ! in_array($normalized, $allowedTypes, true)) {
                continue;
            }

            $types[] = $normalized;
        }

        $types = array_values(array_unique($types));

        if ($types === []) {
            $types = $allowedTypes;
        }

        $rawFilters = $input['filters'] ?? [];
        if (! is_array($rawFilters)) {
            $rawFilters = [];
        }

        /** @var array<string, mixed> $filters */
        $filters = [];

        foreach ($rawFilters as $key => $value) {
            // Only accept string keys to avoid sparse or numeric filter buckets.
            if (! is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value)) {
                // Normalise array filters by removing nulls and stringifying scalars.
                $filters[$key] = array_values(array_filter(array_map(
                    static fn ($item) => is_scalar($item) ? (string) $item : null,
                    $value
                ), static fn ($item) => $item !== null));

                continue;
            }

            if ($value === null) {
                $filters[$key] = null;

                continue;
            }

            if (is_scalar($value)) {
                $filters[$key] = (string) $value;
            }
        }

        $sortValue = $input['sort'] ?? self::ALLOWED_SORTS[0];
        if (! is_string($sortValue)) {
            $sortValue = self::ALLOWED_SORTS[0];
        }

        $sort = in_array($sortValue, self::ALLOWED_SORTS, true)
            ? $sortValue
            : self::ALLOWED_SORTS[0];

        /** @var array<string, mixed> $filteredContext */
        $filteredContext = Arr::where($context, static fn ($value): bool => $value !== null);

        return new self($query, $page, $perPage, $types, $filters, $sort, $filteredContext);
    }

    public function query(): string
    {
        return $this->query;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return array<int, string>
     */
    public function types(): array
    {
        return $this->types;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Provide an immutable representation for downstream consumers.
     *
     * @return array{query:string,page:int,per_page:int,types:array<int,string>,filters:array<string,mixed>,sort:string}
     */
    public function toArray(): array
    {
        return [
            'query'    => $this->query,
            'page'     => $this->page,
            'per_page' => $this->perPage,
            'types'    => $this->types,
            'filters'  => $this->filters,
            'sort'     => $this->sort,
        ];
    }

    /**
     * Clone the data object with a different query string while preserving filters, pagination, and sort.
     */
    public function withQuery(string $query): self
    {
        return new self($query, $this->page, $this->perPage, $this->types, $this->filters, $this->sort, $this->context);
    }
}
