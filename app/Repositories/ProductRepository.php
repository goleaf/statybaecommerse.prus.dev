<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\UseCases\Product\InvalidateProductCache;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class ProductRepository
{
    private const SEARCH_CACHE_TTL_MINUTES = 5;
    private const SHOW_CACHE_TTL_MINUTES = 5;

    public function __construct(private readonly CacheRepository $cache)
    {
    }

    /**
     * Retrieve visible products for the catalog with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginateCatalog(array $filters, int $perPage, int $page, string $sortBy, string $sortDirection): LengthAwarePaginator
    {
        $sortColumn = $this->sanitizeSortColumn($sortBy);
        $direction = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $query = Product::query()
            ->visible()
            ->readyForCatalog()
            ->withCatalogRelations();

        if (! empty($filters['category'])) {
            $query->whereHas('categories', function ($builder) use ($filters): void {
                $builder->where('slug', $filters['category']);
            });
        }

        if (! empty($filters['brand'])) {
            $query->whereHas('brand', function ($builder) use ($filters): void {
                $builder->where('slug', $filters['brand']);
            });
        }

        if (! empty($filters['search'])) {
            $query->searchTerm($filters['search']);
        }

        return $query
            ->orderBy($sortColumn, $direction)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Search visible products with eager loaded relations for autocomplete style endpoints.
     */
    public function searchVisible(string $term, int $limit): Collection
    {
        $version = $this->cacheVersion(InvalidateProductCache::SEARCH_VERSION_KEY);
        $cacheKey = sprintf('products:search:%s:%s:%d', $version, md5($term), $limit);

        return $this->cache->remember($cacheKey, now()->addMinutes(self::SEARCH_CACHE_TTL_MINUTES), function () use ($term, $limit) {
            $timeout = now()->addSeconds(10);

            return Product::query()
                ->visible()
                ->readyForCatalog()
                ->searchTerm($term)
                ->withSearchRelations()
                ->orderBy('name')
                ->cursor()
                ->takeUntilTimeout($timeout)
                ->take($limit)
                ->collect();
        });
    }

    /**
     * Load a single product with its show relations, cached for hot paths.
     */
    public function findVisibleBySlug(string $slug): ?Product
    {
        $version = $this->cacheVersion(InvalidateProductCache::SHOW_VERSION_KEY);
        $cacheKey = sprintf('products:show:%s:%s', $version, $slug);

        return $this->cache->remember($cacheKey, now()->addMinutes(self::SHOW_CACHE_TTL_MINUTES), function () use ($slug) {
            return Product::query()
                ->visible()
                ->readyForCatalog()
                ->where('slug', $slug)
                ->withShowRelations()
                ->first();
        });
    }

    private function cacheVersion(string $key): string
    {
        return $this->cache->rememberForever($key, static fn (): string => Str::uuid()->toString());
    }

    private function sanitizeSortColumn(string $column): string
    {
        $allowed = ['name', 'price', 'sale_price', 'published_at', 'created_at'];

        return \in_array($column, $allowed, true) ? $column : 'name';
    }
}
