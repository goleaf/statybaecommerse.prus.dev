<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Models\Product;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class InvalidateProductCache
{
    use ResolvesSupportedLocales;

    private const SEARCH_VERSION_KEY = 'products:cache:search-version';

    private const SHOW_VERSION_KEY = 'products:cache:show-version';

    /**
     * Flush product related caches for both aggregate metrics and storefront widgets.
     */
    public function __invoke(?Product $product = null): void
    {
        // Accept the product instance for compatibility with orchestrators that
        // supply the touched model, even though the current cache mutation only
        // needs the global catalogue context.
        $usedTags = false;
        if (CacheTagHelper::supportsTags()) {
            Cache::tags([
                CacheKeys::productAggregateTag(),
                CacheKeys::homeTag(),
                CacheKeys::navigationTag(),
            ])->flush();

            $usedTags = true;
        }
        Cache::forget(CacheKeys::productTotalCount());
        Cache::forget(CacheKeys::productVisibleCount());
        if (CacheTagHelper::supportsTags()) {
            Cache::tags(CacheTagHelper::products())->forget(CacheKeys::productTotalCount());
            Cache::tags(CacheTagHelper::products())->forget(CacheKeys::productVisibleCount());
        }
        foreach ($this->knownProductLimits() as $limit) {
            Cache::forget(CacheKeys::productFeaturedList($limit));
            Cache::forget(CacheKeys::productLatestList($limit));

            if (CacheTagHelper::supportsTags()) {
                Cache::tags(CacheTagHelper::products())->forget(CacheKeys::productFeaturedList($limit));
                Cache::tags(CacheTagHelper::products())->forget(CacheKeys::productLatestList($limit));
            }
        }
        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeFeaturedProducts($locale));
            Cache::forget(CacheKeys::homeLatestProducts($locale));

            if (CacheTagHelper::supportsTags()) {
                $productLocaleTags = CacheTagHelper::merge(
                    CacheTagHelper::products(),
                    CacheTagHelper::locale($locale)
                );

                Cache::tags($productLocaleTags)->forget(CacheKeys::homeFeaturedProducts($locale));
                Cache::tags($productLocaleTags)->forget(CacheKeys::homeLatestProducts($locale));
            }
        }
        if (! $usedTags) {
            Log::debug('Product caches invalidated via fallback path.');
        }
        // Bump the immutable cache version markers so array-store caches relying
        // on versioned keys immediately resolve fresh payloads without requiring
        // additional coordination from the observers or calling services.
        $this->bumpVersion(self::SEARCH_VERSION_KEY);
        $this->bumpVersion(self::SHOW_VERSION_KEY);
    }

    /**
     * @return array<int, int>
     */
    private function knownProductLimits(): array
    {
        return [4, 6, 8, 10, 12];
    }

    private function bumpVersion(string $key): void
    {
        // Using UUID values guarantees downstream caches detect a change even
        // when multiple invalidation events occur within the same second.
        Cache::forever($key, Str::uuid()->toString());
    }
}
