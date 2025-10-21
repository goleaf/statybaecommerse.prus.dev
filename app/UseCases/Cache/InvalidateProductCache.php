<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class InvalidateProductCache
{
    use ResolvesSupportedLocales;

    /**
     * Flush product related caches for both aggregate metrics and storefront widgets.
     */
    public function __invoke(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([
                CacheKeys::productAggregateTag(),
                CacheKeys::homeTag(),
                CacheKeys::navigationTag(),
            ])->flush();

            return;
        }

        Cache::forget(CacheKeys::productTotalCount());
        Cache::forget(CacheKeys::productVisibleCount());

        foreach ($this->knownProductLimits() as $limit) {
            Cache::forget(CacheKeys::productFeaturedList($limit));
            Cache::forget(CacheKeys::productLatestList($limit));
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeFeaturedProducts($locale));
            Cache::forget(CacheKeys::homeLatestProducts($locale));
        }

        Log::debug('Product caches invalidated via fallback path.');
    }

    /**
     * @return array<int, int>
     */
    private function knownProductLimits(): array
    {
        return [4, 6, 8, 10, 12];
    }
}
