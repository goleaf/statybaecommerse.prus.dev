<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

/**
 * Service for efficiently computing facet counts with query budget enforcement.
 *
 * Implements Requirements 4.1, 4.2: Eliminate N+1 aggregate queries on category filters
 */
final class FacetCountingService
{
    private const MAX_QUERIES_PER_REQUEST = 6;

    private int $queryCount = 0;

    /**
     * Get brand facet counts using aggregated queries.
     *
     * @param  Builder                                              $baseQuery The base product query with filters applied
     * @return array<int, array{id: int, name: string, count: int}>
     */
    public function getBrandFacets(Builder $baseQuery): array
    {
        $this->enforceQueryBudget();

        // Single aggregated query to get brand counts
        $brandCounts = (clone $baseQuery)
            ->selectRaw('brand_id, COUNT(*) as product_count')
            ->whereNotNull('brand_id')
            ->groupBy('brand_id')
            ->pluck('product_count', 'brand_id')
            ->all();

        $this->incrementQueryCount();

        // Get brand details in a single query
        $brands = Brand::query()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->incrementQueryCount();

        return $brands
            ->map(static fn (Brand $brand): array => [
                'id'    => (int) $brand->id,
                'name'  => (string) $brand->name,
                'count' => (int) ($brandCounts[$brand->id] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Get collection facet counts using aggregated queries.
     *
     * @param  Builder                                              $baseQuery The base product query with filters applied
     * @return array<int, array{id: int, name: string, count: int}>
     */
    public function getCollectionFacets(Builder $baseQuery): array
    {
        $this->enforceQueryBudget();

        // Single aggregated query to get collection counts via pivot table
        $collectionCounts = (clone $baseQuery)
            ->join('product_collections', 'products.id', '=', 'product_collections.product_id')
            ->selectRaw('product_collections.collection_id, COUNT(DISTINCT products.id) as product_count')
            ->groupBy('product_collections.collection_id')
            ->pluck('product_count', 'collection_id')
            ->all();

        $this->incrementQueryCount();

        // Get collection details in a single query
        $collections = Collection::query()
            ->visible()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->incrementQueryCount();

        return $collections
            ->map(static fn (Collection $collection): array => [
                'id'    => (int) $collection->id,
                'name'  => (string) $collection->name,
                'count' => (int) ($collectionCounts[$collection->id] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Get category facet counts using aggregated queries.
     *
     * @param  Builder                                              $baseQuery The base product query with filters applied
     * @return array<int, array{id: int, name: string, count: int}>
     */
    public function getCategoryFacets(Builder $baseQuery): array
    {
        $this->enforceQueryBudget();

        // Single aggregated query to get category counts via pivot table
        $categoryCounts = (clone $baseQuery)
            ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
            ->selectRaw('product_categories.category_id, COUNT(DISTINCT products.id) as product_count')
            ->groupBy('product_categories.category_id')
            ->pluck('product_count', 'category_id')
            ->all();

        $this->incrementQueryCount();

        // Get category details in a single query
        $categories = Category::query()
            ->visible()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->incrementQueryCount();

        return $categories
            ->map(static fn (Category $category): array => [
                'id'    => (int) $category->id,
                'name'  => (string) $category->name,
                'count' => (int) ($categoryCounts[$category->id] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Get all facet counts in a single method call for maximum efficiency.
     *
     * @param  Builder                                                     $baseQuery The base product query with filters applied
     * @return array{brands: array, collections: array, categories: array}
     */
    public function getAllFacets(Builder $baseQuery): array
    {
        return [
            'brands'      => $this->getBrandFacets($baseQuery),
            'collections' => $this->getCollectionFacets($baseQuery),
            'categories'  => $this->getCategoryFacets($baseQuery),
        ];
    }

    /**
     * Reset query count for new request.
     */
    public function resetQueryCount(): void
    {
        $this->queryCount = 0;
    }

    /**
     * Get current query count.
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Enforce query budget to prevent N+1 patterns.
     */
    private function enforceQueryBudget(): void
    {
        if ($this->queryCount >= self::MAX_QUERIES_PER_REQUEST) {
            throw new RuntimeException(
                sprintf(
                    'Query budget exceeded: %d queries already executed (max: %d). This indicates a potential N+1 query pattern.',
                    $this->queryCount,
                    self::MAX_QUERIES_PER_REQUEST
                )
            );
        }
    }

    /**
     * Increment query count for budget tracking.
     */
    private function incrementQueryCount(): void
    {
        $this->queryCount++;
    }
}
