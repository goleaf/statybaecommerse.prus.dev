<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Models\Product;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class InvalidateProductCache
{
    use ResolvesSupportedLocales;

    public function __construct(private readonly CacheInvalidationService $cacheInvalidationService) {}

    /**
     * Flush product related caches for both aggregate metrics and storefront widgets.
     */
    public function __invoke(?Product $product = null): void
    {
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
    }

    /**
     * @return array<int, int>
     */
    private function knownProductLimits(): array
    {
        return [4, 6, 8, 10, 12];
    }
}
