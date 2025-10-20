<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Arr;
use InvalidArgumentException;

final class SearchQueryData
{
    public const DEFAULT_PER_PAGE = 10;

    public const MAX_PER_PAGE = 25;

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
    private readonly array $context;

    /**
     * @param  array<int, string>  $types
     * @param  array<string, mixed>  $context
     */
    private function __construct(string $query, int $page, int $perPage, array $types, array $context)
    {
        $this->query = $query;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->types = $types;
        $this->context = $context;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $context
     */
    public static function fromArray(array $input, array $context = []): self
    {
        $rawQuery = (string) ($input['query'] ?? $input['q'] ?? '');
        $query = trim(preg_replace('/\s+/', ' ', $rawQuery) ?? '');

        if ($query === '') {
            throw new InvalidArgumentException('Search query must be provided.');
        }

        $page = (int) ($input['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $perPage = (int) ($input['per_page'] ?? self::DEFAULT_PER_PAGE);
        if ($perPage < 1) {
            $perPage = 1;
        }

        $perPage = min($perPage, self::MAX_PER_PAGE);

        $rawTypes = $input['types'] ?? ['product', 'category', 'brand'];
        if (! is_array($rawTypes)) {
            $rawTypes = [$rawTypes];
        }

        $allowedTypes = ['product', 'category', 'brand'];
        $types = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): ?string => is_string($value) && in_array($value, $allowedTypes, true)
                ? $value
                : null,
            $rawTypes
        ))));

        if ($types === []) {
            $types = $allowedTypes;
        }

        $filteredContext = Arr::where($context, static fn ($value): bool => $value !== null);

        return new self($query, $page, $perPage, $types, $filteredContext);
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
    public function context(): array
    {
        return $this->context;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
