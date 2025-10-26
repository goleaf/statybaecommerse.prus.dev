<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SearchQueryData;
use App\Repositories\Search\BrandSearchRepository;
use App\Repositories\Search\CategorySearchRepository;
use App\Repositories\Search\ProductSearchRepository;
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
        $sanitised = $this->sanitizeQueryString($originalQuery);
        $normalizedQuery = $sanitised['query'];

        if ($sanitised['blocked'] || $normalizedQuery === '') {
            return $isAggregated
                ? $this->blockedAggregatedPayload($queryData, $originalQuery)
                : [];
        }

        if ($sanitised['modified']) {
            // Swap in the sanitised query so downstream components only execute safe tokens.
            $queryData = $queryData->withQuery($normalizedQuery);
        }

        $cachePayload = array_merge($queryData->context(), [
            'page'     => $queryData->page(),
            'per_page' => $queryData->perPage(),
            'types'    => $queryData->types(),
            'filters'  => $queryData->filters(),
            'sort'     => $queryData->sort(),
            'locale'   => app()->getLocale(),
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
            'product'  => count($buckets['product'] ?? []),
            'category' => count($buckets['category'] ?? []),
            'brand'    => count($buckets['brand'] ?? []),
        ];

        $groupedResults = $this->groupResultsByType($pageResults, $bucketCounts);
        $returnedCount = count($pageResults);
        $dataPayload = $isAggregated
            ? $this->mergeFlatResults($groupedResults, $pageResults)
            : $pageResults;

        $payload = [
            'data' => $dataPayload,
            'meta' => [
                'query'         => $originalQuery,
                'page'          => $queryData->page(),
                'per_page'      => $queryData->perPage(),
                'max_per_page'  => SearchQueryData::MAX_PER_PAGE,
                'total_results' => $total,
                'returned'      => $returnedCount,
                'has_more'      => ($offset + count($pageResults)) < $total,
                'took_ms'       => (int) round((microtime(true) - $started) * 1000),
                'types'         => $queryData->types(),
                'filters'       => $queryData->filters(),
                'sort'          => $queryData->sort(),
                'cached'        => false,
            ],
            'buckets'    => $bucketCounts,
            'correction' => null,
        ];

        if ($total === 0) {
            $payload['correction'] = $this->buildCorrectionPayload($originalQuery, $queryData->query());
        }

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
            'query'    => $query,
            'page'     => 1,
            'per_page' => $perPage,
            'types'    => ['product', 'category', 'brand'],
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
            'product'  => max(1, (int) ceil($perPage * self::PRODUCT_RATIO * 2)),
            'category' => max(1, (int) ceil($perPage * self::CATEGORY_RATIO * 2)),
            'brand'    => max(1, (int) ceil($perPage * self::BRAND_RATIO * 2)),
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

    private function blockedAggregatedPayload(SearchQueryData $queryData, ?string $originalQuery = null): array
    {
        $bucketCounts = [
            'product'  => 0,
            'category' => 0,
            'brand'    => 0,
        ];

        return [
            'data' => [],
            'meta' => [
                'query'         => $originalQuery ?? $queryData->query(),
                'page'          => $queryData->page(),
                'per_page'      => $queryData->perPage(),
                'max_per_page'  => SearchQueryData::MAX_PER_PAGE,
                'total_results' => 0,
                'returned'      => 0,
                'has_more'      => false,
                'took_ms'       => 0,
                'types'         => $queryData->types(),
                'filters'       => $queryData->filters(),
                'sort'          => $queryData->sort(),
                'cached'        => false,
                'blocked'       => true,
            ],
            'buckets'      => $bucketCounts,
            'aggregations' => $this->groupResultsByType([], $bucketCounts),
            'correction'   => null,
        ];
    }

    /**
     * Build the standard aggregated payload for the suspicious query branch.
     */
    private function emptyAggregatedPayload(SearchQueryData $queryData, ?string $originalQuery = null): array
    {
        $bucketCounts = [
            'product'  => 0,
            'category' => 0,
            'brand'    => 0,
        ];

        // We keep the meta structure identical to successful searches so the
        // API contract remains predictable for callers.
        return [
            'data' => $this->groupResultsByType([], $bucketCounts),
            'meta' => [
                'query'         => $originalQuery ?? $queryData->query(),
                'page'          => $queryData->page(),
                'per_page'      => $queryData->perPage(),
                'max_per_page'  => SearchQueryData::MAX_PER_PAGE,
                'total_results' => 0,
                'returned'      => 0,
                'has_more'      => false,
                'took_ms'       => 0,
                'types'         => $queryData->types(),
                'cached'        => false,
            ],
            'buckets' => $bucketCounts,
        ];
    }

    private function mergeFlatResults(array $groupedResults, array $pageResults): array
    {
        if ($pageResults === []) {
            return $groupedResults;
        }

        $merged = $groupedResults;

        foreach (array_values($pageResults) as $index => $result) {
            $merged[(string) $index] = $result;
        }

        return $merged;
    }

    /**
     * @param  array<int, array<string, mixed>>                                                                                                                                                                                     $results
     * @param  array{product:int,category:int,brand:int}                                                                                                                                                                            $bucketCounts
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
                'product'  => 'products',
                'category' => 'categories',
                'brand'    => 'brands',
                default    => null,
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
     * @param  array{product:int,category:int,brand:int}                                                                                                                                                                            $bucketCounts
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

    /**
     * Derive a correction suggestion for zero-result searches so the client can offer a retry UX.
     */
    private function buildCorrectionPayload(string $originalQuery, string $normalizedQuery): ?array
    {
        $candidates = [];

        if ($normalizedQuery !== '' && mb_strtolower($normalizedQuery) !== mb_strtolower($originalQuery)) {
            $candidates[] = $normalizedQuery;
        }

        $fuzzy = $this->generateFuzzySuggestion($normalizedQuery);

        if ($fuzzy !== null && ! in_array($fuzzy, $candidates, true) && mb_strtolower($fuzzy) !== mb_strtolower($originalQuery)) {
            $candidates[] = $fuzzy;
        }

        if ($candidates === []) {
            return null;
        }

        return [
            'suggested_query' => $candidates[0],
            'alternatives'    => array_values(array_slice($candidates, 1)),
            'applied'         => false,
            'reason'          => 'no_results_fuzzy_match',
        ];
    }

    /**
     * Generate a simple fuzzy variant by removing diacritics and collapsing repeated characters.
     */
    private function generateFuzzySuggestion(string $query): ?string
    {
        $ascii = Str::ascii($query);
        $deduped = preg_replace('/(.)\\1{2,}/u', '$1$1', $ascii ?? '') ?? '';
        $collapsed = trim(preg_replace('/\s+/', ' ', $deduped) ?? '');

        if ($collapsed === '' || mb_strtolower($collapsed) === mb_strtolower($query)) {
            return null;
        }

        return $collapsed;
    }

    /**
     * @return array{query:string, blocked:bool, modified:bool}
     */
    private function sanitizeQueryString(string $query): array
    {
        $original = trim($query);

        if ($original === '') {
            return [
                'query'    => '',
                'blocked'  => false,
                'modified' => false,
            ];
        }

        $normalized = str_replace(["\r", "\n", "\t"], ' ', $original);

        $blocked = false;
        $modified = false;

        $normalized = preg_replace('/(--|\\/\\*|\\*\\/)/', ' ', $normalized ?? '', -1, $commentCount);
        if (($commentCount ?? 0) > 0) {
            $modified = true;
        }

        $normalized = str_replace([';', '"', '`'], ' ', $normalized ?? '');

        $tokens = preg_split('/\\s+/', $normalized ?? '', -1, PREG_SPLIT_NO_EMPTY);
        $safeTokens = [];

        $reservedKeywords = ['select', 'insert', 'update', 'delete', 'drop', 'union', 'where', 'from', 'join'];
        $logicalOperators = ['or', 'and'];

        foreach ($tokens as $token) {
            $stripped = trim($token, " \t\n\r\0\x0B\"'");

            if ($stripped === '') {
                $modified = true;

                continue;
            }

            $lower = mb_strtolower($stripped, 'UTF-8');

            if (str_contains($stripped, '%') || str_contains($stripped, '_')) {
                $blocked = true;
                $modified = true;

                continue;
            }

            if (preg_match('/[=<>]/', $stripped) === 1) {
                $modified = true;

                continue;
            }

            if (in_array($lower, $reservedKeywords, true)) {
                $blocked = true;
                $modified = true;

                continue;
            }

            if (in_array($lower, $logicalOperators, true)) {
                $modified = true;

                continue;
            }

            if (preg_match('/^[\\p{L}\\p{N}\\-\\+\\/]+$/u', $stripped) !== 1) {
                $modified = true;

                continue;
            }

            $safeTokens[] = $stripped;
        }

        $cleaned = implode(' ', $safeTokens);

        if ($cleaned === '') {
            $blocked = true;
        }

        return [
            'query'    => $cleaned,
            'blocked'  => $blocked,
            'modified' => $modified || $cleaned !== $original,
        ];
    }
}
