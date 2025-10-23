<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class InvalidateCategoryCache
{
    public function __construct(private readonly CacheInvalidationService $cacheInvalidationService) {}

    public function __invoke(?Category $category = null): void
    {
        $usedTags = false;

        if (CacheTagHelper::supportsTags()) {
            $tags = [CacheKeys::navigationTag(), CacheKeys::homeTag()];

            if ($categoryId !== null) {
                $tags[] = CacheKeys::categoryTag($categoryId);
            }

            Cache::tags($tags)->flush();

            $usedTags = true;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));
            Cache::forget(CacheKeys::navigationCategories(8, $locale));

            if (CacheTagHelper::supportsTags()) {
                $categoryLocaleTags = CacheTagHelper::merge(
                    CacheTagHelper::categories(),
                    CacheTagHelper::locale($locale)
                );

                Cache::tags($categoryLocaleTags)->forget(CacheKeys::homeCategoryTree($locale));
                Cache::tags($categoryLocaleTags)->forget(CacheKeys::homeCatalogueCategories($locale));
                Cache::tags($categoryLocaleTags)->forget(CacheKeys::navigationCategories(8, $locale));
            }
        }

        // Reset the raw navigation tree cache so menu builders are refreshed
        // alongside the locale-specific payloads handled above.
        Cache::forget(CacheKeys::categoryNavigationTree());

        if (CacheTagHelper::supportsTags()) {
            Cache::tags(CacheTagHelper::categories())->forget(CacheKeys::categoryNavigationTree());
        }

        if (! $usedTags) {
            Log::debug('Category caches invalidated via fallback path.', [
                'category_id' => $categoryId,
            ]);
        }
    }
}
