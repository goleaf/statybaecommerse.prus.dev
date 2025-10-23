<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SearchQueryData;
use App\Repositories\Search\BrandSearchRepository;
use App\Repositories\Search\CategorySearchRepository;
use App\Repositories\Search\ProductSearchRepository;
use App\Services\Search\ScoutSearchEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SearchService
{
    private const PRODUCT_RATIO = 0.6;

    private const CATEGORY_RATIO = 0.25;

    private const BRAND_RATIO = 0.15;

    public function __construct(
        private readonly ProductSearchRepository $productRepository,
        private readonly CategorySearchRepository $categoryRepository,
        private readonly BrandSearchRepository $brandRepository,
        private readonly SearchRankingService $rankingService,
        private readonly SearchCacheService $cacheService
    ) {}

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(string|SearchQueryData $query, ?int $limit = null): array
    {
        $isAggregated = $query instanceof SearchQueryData;
        $queryData = $isAggregated
            ? $query
            : $this->legacyQueryData((string) $query, $limit);
        $originalQuery = $queryData->query();
        // Flag SQL injection markers early so we can short-circuit strict API callers while still serving legacy lookups.
        $isSuspicious = $this->isSuspiciousQuery($originalQuery);
        $normalizedQuery = $this->sanitizeQueryString($originalQuery);

        if ($isSuspicious && ($queryData->context()['strict_sanitization'] ?? false)) {
            return $isAggregated
                ? $this->emptyAggregatedPayload($queryData, $originalQuery)
                : [];
        }

        if ($normalizedQuery === '') {
            return $isAggregated
                ? $this->emptyAggregatedPayload($queryData, $originalQuery)
                : [];
        }

        if ($normalizedQuery !== $originalQuery) {
            $queryData = SearchQueryData::fromArray([
                'query'    => $normalizedQuery,
                'page'     => $queryData->page(),
                'per_page' => $queryData->perPage(),
                'types'    => $queryData->types(),
            ], $queryData->context());
        }

        $cachePayload = array_merge($queryData->context(), [
            'page' => $queryData->page(),
            'per_page' => $queryData->perPage(),
            'types' => $queryData->types(),
            'locale' => app()->getLocale(),
        ]);
        $cacheKey = $this->cacheService->generateCacheKey($queryData->query(), $cachePayload);

        $cached = $this->cacheService->getCachedResults($cacheKey);

        if (is_array($cached)) {
            if (isset($cached['meta']) && is_array($cached['meta'])) {
                $cached['meta']['cached'] = true;
            }

            return $isAggregated ? $cached : ($cached['data'] ?? []);
        }

        $started = microtime(true);
        $buckets = $this->collectBuckets($queryData);
        $merged = [];

        foreach ($buckets as $results) {
            $merged = array_merge($merged, $results);
        }
        $ranked = $this->rankingService->rankResults($merged, $queryData->query(), $queryData->context());
        $total = count($ranked);
        $offset = $queryData->offset();
        $pageResults = array_slice($ranked, $offset, $queryData->perPage());

        $bucketCounts = [
            'product' => count($buckets['product'] ?? []),
            'category' => count($buckets['category'] ?? []),
            'brand' => count($buckets['brand'] ?? []),
        ];

        $groupedResults = $this->groupResultsByType($pageResults, $bucketCounts);
        $returnedCount = $isAggregated
            ? array_sum(array_map(static fn (array $bucket): int => count($bucket['items']), $groupedResults))
            : count($pageResults);

        $payload = [
            'data' => $isAggregated ? $groupedResults : $pageResults,
            'meta' => [
                'query' => $originalQuery,
                'page' => $queryData->page(),
                'per_page' => $queryData->perPage(),
                'max_per_page' => SearchQueryData::MAX_PER_PAGE,
                'total_results' => $total,
                'returned' => $returnedCount,
                'has_more' => ($offset + count($pageResults)) < $total,
                'took_ms' => (int) round((microtime(true) - $started) * 1000),
                'types' => $queryData->types(),
                'cached' => false,
            ],
            'buckets' => $bucketCounts,
        ];

        $this->cacheService->cacheSearchResults($cacheKey, $payload, $queryData->query(), $cachePayload);

        return $isAggregated ? $payload : $payload['data'];
    }

    /**
     * Handle clearCache functionality with proper error handling.
     */
    public function clearCache(): void
    {
        $this->cache->flush();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function collectBuckets(SearchQueryData $queryData): array
    {
        if ($this->shouldUseScout()) {
            return $this->collectBucketsUsingScout($queryData);
        }

        return $this->collectBucketsUsingDatabase($queryData);
    }

    private function legacyQueryData(string $query, ?int $limit): SearchQueryData
    {
        $perPage = $limit ?? SearchQueryData::DEFAULT_PER_PAGE;

        return SearchQueryData::fromArray([
            'query' => $query,
            'page' => 1,
            'per_page' => $perPage,
            'types' => ['product', 'category', 'brand'],
        ], [
            'source' => 'legacy-search',
            'locale' => app()->getLocale(),
        ]);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function collectBucketsUsingDatabase(SearchQueryData $queryData): array
    {
        $limits = $this->resolveBucketLimits($queryData->perPage());

        $buckets = [];

        foreach ($queryData->types() as $type) {
            if ($type === 'product') {
                $buckets['product'] = $this->productRepository->search($queryData, $limits['product']);
            }

            if ($type === 'category') {
                $buckets['category'] = $this->categoryRepository->search($queryData, $limits['category']);
            }

            if ($type === 'brand') {
                $buckets['brand'] = $this->brandRepository->search($queryData, $limits['brand']);
            }
        }

        return $buckets;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function collectBucketsUsingScout(SearchQueryData $queryData): array
    {
        $limits = $this->resolveBucketLimits($queryData->perPage());

        $buckets = [];

        foreach ($queryData->types() as $type) {
            if ($type === 'product') {
                $buckets['product'] = $this->scoutSearchEngine->searchProducts($queryData, $limits['product']);
            }

            if ($type === 'category') {
                $buckets['category'] = $this->scoutSearchEngine->searchCategories($queryData, $limits['category']);
            }

            if ($type === 'brand') {
                $buckets['brand'] = $this->scoutSearchEngine->searchBrands($queryData, $limits['brand']);
            }
        }

        return $buckets;
    }

    /**
     * @return array{product: int, category: int, brand: int}
     */
    private function resolveBucketLimits(int $perPage): array
    {
        $limits = [
            'product' => max(1, (int) ceil($perPage * self::PRODUCT_RATIO * 2)),
            'category' => max(1, (int) ceil($perPage * self::CATEGORY_RATIO * 2)),
            'brand' => max(1, (int) ceil($perPage * self::BRAND_RATIO * 2)),
        ];

        $limits['product'] = max($limits['product'], $perPage);
        $limits['category'] = max($limits['category'], (int) ceil($perPage / 2));
        $limits['brand'] = max($limits['brand'], (int) ceil($perPage / 2));

        return $limits;
    }

    private function shouldUseScout(): bool
    {
        return config('search.driver') === 'scout' && config('search.scout.enabled');
    }

    /**
     * Build the standard aggregated payload for the suspicious query branch.
     */
    private function emptyAggregatedPayload(SearchQueryData $queryData, ?string $originalQuery = null): array
    {
        $bucketCounts = [
            'product' => 0,
            'category' => 0,
            'brand' => 0,
        ];

        // We keep the meta structure identical to successful searches so the
        // API contract remains predictable for callers.
        return [
            'data' => $this->groupResultsByType([], $bucketCounts),
            'meta' => [
                'query' => $originalQuery ?? $queryData->query(),
                'page' => $queryData->page(),
                'per_page' => $queryData->perPage(),
                'max_per_page' => SearchQueryData::MAX_PER_PAGE,
                'total_results' => 0,
                'returned' => 0,
                'has_more' => false,
                'took_ms' => 0,
                'types' => $queryData->types(),
                'cached' => false,
            ],
            'buckets' => $bucketCounts,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @param  array{product:int,category:int,brand:int}  $bucketCounts
     * @return array{products: array{items: array<int, array<string, mixed>>, total:int}, categories: array{items: array<int, array<string, mixed>>, total:int}, brands: array{items: array<int, array<string, mixed>>, total:int}}
     */
    private function groupResultsByType(array $results, array $bucketCounts): array
    {
        $buckets = $this->bucketSkeleton($bucketCounts);

        foreach ($results as $result) {
            if (! is_array($result)) {
                continue;
            }

            $key = match ($result['type'] ?? null) {
                'product' => 'products',
                'category' => 'categories',
                'brand' => 'brands',
                default => null,
            };

            if ($key === null) {
                continue;
            }

            $buckets[$key]['items'][] = $result;
        }

        foreach ($buckets as &$bucket) {
            $bucket['items'] = array_values($bucket['items']);
        }
        unset($bucket);

        return $buckets;
    }

    /**
     * @param  array{product:int,category:int,brand:int}  $bucketCounts
     * @return array{products: array{items: array<int, array<string, mixed>>, total:int}, categories: array{items: array<int, array<string, mixed>>, total:int}, brands: array{items: array<int, array<string, mixed>>, total:int}}
     */
    private function bucketSkeleton(array $bucketCounts): array
    {
        return [
            'products' => [
                'items' => [],
                'total' => $bucketCounts['product'] ?? 0,
            ],
            'categories' => [
                'items' => [],
                'total' => $bucketCounts['category'] ?? 0,
            ],
            'brands' => [
                'items' => [],
                'total' => $bucketCounts['brand'] ?? 0,
            ],
        ];
    }

    private function sanitizeQueryString(string $query): string
    {
        $cleaned = preg_replace('/(--|\\/\\*|\\*\\/)/', ' ', $query);
        $cleaned = preg_replace('/[\'";=#]/', ' ', $cleaned ?? '');
        $cleaned = preg_replace('/[^\\p{L}\\p{N}\\s]/u', ' ', $cleaned ?? '');
        // Drop common boolean injection suffixes (e.g. " OR 1=1") so legitimate search terms survive legacy requests.
        $cleaned = preg_replace('/\b(?:or|and)\b\s+\d+(?:\s*=\s*\d+|\s+\d+)?/iu', ' ', $cleaned ?? '');
        $cleaned = preg_replace('/\\s+/u', ' ', $cleaned ?? '');

        return trim($cleaned ?? '');
    }

    private function isSuspiciousQuery(string $query): bool
    {
        // Normalise the payload once so pattern checks remain deterministic across locales.
        $normalized = mb_strtolower($query);

        $patterns = [
            '/\b(?:or|and)\b\s+\d+\s*=\s*\d+/u',
            '/\bunion\b\s+\bselect\b/u',
            '/;\s*drop\s+table/u',
            '/--/',
            '/\/\*/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        return false;
    }
}
