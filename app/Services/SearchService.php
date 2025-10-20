<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SearchQueryData;
use App\Repositories\Search\BrandSearchRepository;
use App\Repositories\Search\CategorySearchRepository;
use App\Repositories\Search\ProductSearchRepository;
use App\Services\Search\ScoutSearchEngine;
use Illuminate\Support\Facades\Cache;

/**
 * SearchService
 *
 * Service class containing SearchService business logic, external integrations, and complex operations with proper error handling and logging.
 */
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
        private readonly SearchCacheService $cacheService,
        private readonly ScoutSearchEngine $scoutSearchEngine
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

        $payload = [
            'data' => $pageResults,
            'meta' => [
                'query' => $queryData->query(),
                'page' => $queryData->page(),
                'per_page' => $queryData->perPage(),
                'max_per_page' => SearchQueryData::MAX_PER_PAGE,
                'total_results' => $total,
                'returned' => count($pageResults),
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
        Cache::flush();
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
}
