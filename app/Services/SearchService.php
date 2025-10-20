<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SearchQueryData;
use App\Repositories\Search\BrandSearchRepository;
use App\Repositories\Search\CategorySearchRepository;
use App\Repositories\Search\ProductSearchRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

final class SearchService
{
    private CacheRepository $cache;

    public function __construct(
        private readonly ProductSearchRepository $productRepository,
        private readonly CategorySearchRepository $categoryRepository,
        private readonly BrandSearchRepository $brandRepository,
        ?CacheRepository $cache = null,
    ) {
        $this->cache = $cache ?? Cache::store();
    }

    public function aggregate(SearchQueryData $query, array $limits = []): array
    {
        $limits = $this->normalizeLimits($query, $limits);
        $cacheKey = $this->cacheKey($query, $limits);

        return $this->cache->remember($cacheKey, now()->addSeconds(120), function () use ($query, $limits) {
            $products = $this->productRepository->search($query, $limits['products']);
            $categories = $this->categoryRepository->search($query, $limits['categories']);
            $brands = $this->brandRepository->search($query, $limits['brands']);

            return [
                'products' => $products,
                'categories' => $categories,
                'brands' => $brands,
                'meta' => [
                    'query' => $query->q,
                    'sort' => $query->sort(),
                    'page' => $query->page(),
                    'per_page' => $query->perPage(),
                    'filters' => [
                        'brand' => $query->brandIds(),
                        'category' => $query->categoryIds(),
                        'price_min' => $query->price_min,
                        'price_max' => $query->price_max,
                    ],
                ],
            ];
        });
    }

    public function search(string $term, int $limit = 10): array
    {
        $limit = (int) min(max($limit, 1), SearchQueryData::MAX_PER_PAGE);
        $query = new SearchQueryData(q: $term, per_page: $limit);
        $limits = [
            'products' => min((int) ceil($limit * 0.6), SearchQueryData::MAX_PER_PAGE),
            'categories' => min((int) ceil($limit * 0.2), SearchQueryData::MAX_PER_PAGE),
            'brands' => min((int) ceil($limit * 0.2), SearchQueryData::MAX_PER_PAGE),
        ];

        $results = $this->aggregate($query, $limits);

        return collect([$results['products']['items'], $results['categories']['items'], $results['brands']['items']])
            ->collapse()
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(function (array $item) {
                $item['relevance_score'] = $item['score'];
                unset($item['score']);

                return $item;
            })
            ->all();
    }

    public function clearCache(): void
    {
        $this->cache->flush();
    }

    public function clearSearchCache(string $query): void
    {
        $this->cache->flush();
    }

    private function cacheKey(SearchQueryData $query, array $limits): string
    {
        return $this->cacheNamespace().'|'.app()->getLocale().'|'.$query->normalizedCacheKey().'|'.md5(json_encode($limits, JSON_THROW_ON_ERROR));
    }

    private function cacheNamespace(): string
    {
        return 'search:aggregate';
    }

    private function normalizeLimits(SearchQueryData $query, array $limits): array
    {
        $defaults = [
            'products' => $query->perPage(),
            'categories' => min(5, $query->perPage()),
            'brands' => min(5, $query->perPage()),
        ];

        $merged = array_merge($defaults, Arr::only($limits, ['products', 'categories', 'brands']));

        return collect($merged)
            ->map(fn ($value) => (int) min(max((int) $value, 1), SearchQueryData::MAX_PER_PAGE))
            ->all();
    }
}
