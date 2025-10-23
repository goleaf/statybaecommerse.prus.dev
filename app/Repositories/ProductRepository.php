<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;

final class ProductRepository
{
    private const SEARCH_CACHE_TTL_MINUTES = 5;
    private const SHOW_CACHE_TTL_MINUTES = 5;

    public function __construct(private readonly CacheRepository $cache)
    {
        $defaultConnection = config('database.default');

        if ($connection !== null && $connection !== $defaultConnection) {
            return Product::on($connection)->newQuery()->count();
        }

        return TagAwareCache::remember(
            CacheKeys::productTotalCount(),
            now()->addSeconds(CacheKeys::TTL_MINUTE),
            static fn (): int => Product::query()->count(),
            [CacheKeys::productAggregateTag(), CacheKeys::dashboardTag()]
        );
    }

    public function visibleCount(): int
    {
        return $this->rememberWithTags(
            CacheKeys::productVisibleCount(),
            CacheKeys::TTL_MINUTE,
            static fn (): int => Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            [CacheKeys::productAggregateTag(), CacheKeys::homeTag()],
        );
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->rememberWithTags(
            CacheKeys::productFeaturedList($limit),
            CacheKeys::TTL_MINUTE,
            static fn (): Collection => Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->where('is_featured', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->limit($limit)
                ->get(),
            [CacheKeys::productAggregateTag(), CacheKeys::homeTag(), CacheKeys::navigationTag()],
        );
    }

    public function latest(int $limit = 8): Collection
    {
        return $this->rememberWithTags(
            CacheKeys::productLatestList($limit),
            CacheKeys::TTL_MINUTE,
            static fn (): Collection => Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('created_at')
                ->limit($limit)
                ->get(),
            [CacheKeys::productAggregateTag(), CacheKeys::homeTag()],
        );
    }

    public function findPublishedById(int $productId): ?Product
    {
        $cacheKey = CacheKeys::productTag($productId);

        return $this->rememberWithTags(
            $cacheKey,
            CacheKeys::TTL_MINUTE,
            static fn () => Product::query()
                ->withoutGlobalScopes()
                ->whereKey($productId)
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->first(),
            [CacheKeys::productAggregateTag()],
        );
    }

    private function rememberWithTags(string $key, int $ttlSeconds, callable $callback, array $tags = []): mixed
    {
        $expiresAt = now()->addSeconds($ttlSeconds);

        if (Cache::supportsTags()) {
            $normalizedTags = array_values(array_unique($tags));

            return Cache::tags($normalizedTags)->remember($key, $expiresAt, $callback);
        }

        return Cache::remember($key, $expiresAt, $callback);
    }
}
