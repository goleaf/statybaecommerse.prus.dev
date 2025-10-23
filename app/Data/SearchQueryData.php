<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\FloatType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

final class SearchQueryData extends Data
{
    public const MAX_PER_PAGE = 50;

    public function __construct(
        #[Required, StringType, Min(1), Max(255)]
        public string $q,
        #[Nullable, FloatType]
        public ?float $price_min = null,
        #[Nullable, FloatType]
        public ?float $price_max = null,
        #[Nullable, ArrayType]
        public array $brand_ids = [],
        #[Nullable, ArrayType]
        public array $category_ids = [],
        #[Nullable, StringType]
        public string $sort = 'relevance',
        #[Nullable, IntegerType, Min(1)]
        public int $page = 1,
        #[Nullable, IntegerType, Min(1), Max(self::MAX_PER_PAGE)]
        public int $per_page = 10,
    ) {
        $this->brand_ids = $this->normalizeIdArray($brand_ids ?? []);
        $this->category_ids = $this->normalizeIdArray($category_ids ?? []);
        $this->sort = $this->normalizeSort($sort);
        $this->page = max(1, $page);
        $this->per_page = $this->normalizePerPage($per_page);

        if ($this->price_min !== null && $this->price_max !== null && $this->price_min > $this->price_max) {
            [$this->price_min, $this->price_max] = [$this->price_max, $this->price_min];
        }
    }

    public static function fromRequest(Request $request): self
    {
        $filters = $request->input('filters', []);

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:255'],
            'filters.price_min' => ['nullable', 'numeric', 'min:0'],
            'filters.price_max' => ['nullable', 'numeric', 'min:0'],
            'filters.brand' => ['nullable', 'array'],
            'filters.brand.*' => ['integer'],
            'filters.category' => ['nullable', 'array'],
            'filters.category.*' => ['integer'],
            'sort' => ['nullable', 'string', 'in:relevance,price,date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ]);

        return new self(
            q: $validated['q'],
            price_min: isset($filters['price_min']) ? (float) $filters['price_min'] : null,
            price_max: isset($filters['price_max']) ? (float) $filters['price_max'] : null,
            brand_ids: Arr::get($filters, 'brand', []),
            category_ids: Arr::get($filters, 'category', []),
            sort: $validated['sort'] ?? 'relevance',
            page: $validated['page'] ?? 1,
            per_page: $validated['per_page'] ?? 10,
        );
    }

    public function normalizedCacheKey(): string
    {
        return md5(json_encode([
            'q' => Str::lower(trim($this->q)),
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'brand_ids' => $this->brand_ids,
            'category_ids' => $this->category_ids,
            'sort' => $this->sort,
            'page' => $this->page,
            'per_page' => $this->per_page,
        ], JSON_THROW_ON_ERROR));
    }

    public function perPage(): int
    {
        return $this->per_page;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function brandIds(): array
    {
        return $this->brand_ids;
    }

    public function categoryIds(): array
    {
        return $this->category_ids;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    private function normalizeIdArray(array $ids): array
    {
        return collect($ids)
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizePerPage(int $perPage): int
    {
        return (int) min(max($perPage, 1), self::MAX_PER_PAGE);
    }

    private function normalizeSort(string $sort): string
    {
        return in_array($sort, ['relevance', 'price', 'date'], true) ? $sort : 'relevance';
    }
}
