<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use App\Support\Cache\TagAwareCache;
use Illuminate\Support\Collection;

final class ProductRepository
{
    /**
     * Cache the total product count for dashboard widgets while allowing raw
     * connection checks used by maintenance commands.
     */
    public function count(?string $connection = null): int
    {
        $defaultConnection = config('database.default');

        // Skip cache when the caller explicitly targets a non-default connection.
        if ($connection !== null && $connection !== $defaultConnection) {
            return Product::on($connection)->newQuery()->count();
        }

        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            CacheTagHelper::dashboards(),
            [CacheKeys::productAggregateTag(), CacheKeys::dashboardTag()],
        );

        return $this->remember(
            CacheKeys::productTotalCount(),
            CacheKeys::TTL_MINUTE,
            static fn (): int => Product::query()->count(),
            $tags,
        );
    }

    /**
     * Count published, visible products for storefront statistics.
     */
    public function visibleCount(): int
    {
        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            [CacheKeys::productAggregateTag()],
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()],
        );

        return $this->remember(
            CacheKeys::productVisibleCount(),
            CacheKeys::TTL_MINUTE,
            static fn (): int => Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            $tags,
        );
    }

    /**
     * Retrieve featured products while caching the list with catalogue tags.
     *
     * @return Collection<int, Product>
     */
    public function featured(int $limit = 8): Collection
    {
        $limit = max(1, $limit);

        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            CacheTagHelper::categories(),
            CacheTagHelper::brands(),
            [CacheKeys::productAggregateTag()],
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()],
        );

        return $this->remember(
            CacheKeys::productFeaturedList($limit),
            CacheKeys::TTL_MINUTE,
            static function () use ($limit): Collection {
                return Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->latest('published_at')
                    ->limit($limit)
                    ->get();
            },
            $tags,
        );
    }

    /**
     * Retrieve the most recent products for carousels and storefront blocks.
     *
     * @return Collection<int, Product>
     */
    public function latest(int $limit = 8): Collection
    {
        $limit = max(1, $limit);

        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            CacheTagHelper::categories(),
            CacheTagHelper::brands(),
            [CacheKeys::productAggregateTag()],
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()],
        );

        return $this->remember(
            CacheKeys::productLatestList($limit),
            CacheKeys::TTL_MINUTE,
            static function () use ($limit): Collection {
                return Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
            },
            $tags,
        );
    }

    /**
     * Resolve a published product by id while ensuring cache invalidation via tags.
     */
    public function findPublishedById(int $productId): ?Product
    {
        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            [CacheKeys::productAggregateTag()],
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()],
            [CacheKeys::productTag($productId)],
        );

        return $this->remember(
            CacheKeys::productTag($productId),
            CacheKeys::TTL_MINUTE,
            static function () use ($productId): ?Product {
                return Product::query()
                    ->withoutGlobalScopes()
                    ->whereKey($productId)
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->first();
            },
            $tags,
        );
    }

    /**
     * Centralised cache helper so repository queries share the same TTL logic.
     *
     * @template TValue
     *
     * @param  callable(): TValue $callback
     * @param  array<int, string> $tags
     * @return TValue
     */
    private function remember(string $key, int $ttlSeconds, callable $callback, array $tags = [])
    {
        // Leverage the tag-aware helper so tagged stores and array stores behave consistently.
        return TagAwareCache::remember($key, now()->addSeconds($ttlSeconds), $callback, $tags);
    }
}
