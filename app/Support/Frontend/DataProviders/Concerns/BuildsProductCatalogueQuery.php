<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders\Concerns;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait BuildsProductCatalogueQuery
{
    /**
     * Base query for visible, published catalogue products with relevant relations eager loaded.
     */
    private function baseProductQuery(): Builder
    {
        return Product::query()
            ->where('is_visible', true)
            ->whereIn('status', ['published', 'active'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'brand:id,name,slug',
                'media',
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => static function (Builder $query): void {
                    $query->where('is_approved', true);
                },
            ], 'rating')
            ->withCount([
                'reviews as approved_reviews_count' => static function (Builder $query): void {
                    $query->where('is_approved', true);
                },
            ]);
    }

    /**
     * Apply keyword, filter and sort modifiers to the provided product query.
     */
    private function applyCatalogueConstraints(Builder $query, array $filters = []): array
    {
        $keyword = trim((string) ($filters['q'] ?? $filters['search'] ?? ''));
        if ($keyword !== '') {
            $query->where(static function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('name', 'like', '%'.Str::lower($keyword).'%')
                    ->orWhere('sku', 'like', '%'.Str::upper($keyword).'%');
            });
        }

        $filter = (string) ($filters['filter'] ?? '');
        if ($filter === 'featured') {
            $query->where('is_featured', true);
        } elseif ($filter === 'sale') {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        }

        $sort = (string) ($filters['sort'] ?? 'latest');
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) desc'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('published_at')->orderBy('name'),
        };

        return [
            'keyword' => $keyword,
            'filter' => $filter,
            'sort' => $sort,
        ];
    }

    /**
     * Resolve the pagination size ensuring sensible defaults and safety guards.
     */
    private function resolvePerPage(array $filters = []): int
    {
        $perPage = (int) ($filters['per_page'] ?? $filters['perPage'] ?? 12);

        if ($perPage < 6) {
            return 6;
        }

        if ($perPage > 60) {
            return 60;
        }

        return $perPage;
    }
}
