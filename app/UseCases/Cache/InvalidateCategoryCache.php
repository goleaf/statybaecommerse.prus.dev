<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Models\Category;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class InvalidateCategoryCache
{
    use ResolvesSupportedLocales;

    private const TREE_VERSION_KEY = 'categories:cache:tree-version';

    public function __invoke(?Category $category = null): void
    {
        $categoryId = $category?->getKey();
        $usedTags = false;

        if (CacheTagHelper::supportsTags()) {
            $tags = [CacheKeys::navigationTag(), CacheKeys::homeTag()];

            if (is_numeric($categoryId)) {
                // Cast to int to satisfy typed cache key helpers while preserving the
                // original identifier semantics from the Eloquent model.
                $tags[] = CacheKeys::categoryTag((int) $categoryId);
            }

            Cache::tags($tags)->flush();

            $usedTags = true;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));
            Cache::forget(CacheKeys::navigationCategories(8, $locale));
            Cache::forget(CacheKeys::categoryNavigationTree() . ':' . $locale);

            if (CacheTagHelper::supportsTags()) {
                $categoryLocaleTags = CacheTagHelper::merge(
                    CacheTagHelper::categories(),
                    CacheTagHelper::locale($locale)
                );

                Cache::tags($categoryLocaleTags)->forget(CacheKeys::homeCategoryTree($locale));
                Cache::tags($categoryLocaleTags)->forget(CacheKeys::homeCatalogueCategories($locale));
                Cache::tags($categoryLocaleTags)->forget(CacheKeys::navigationCategories(8, $locale));
                Cache::tags($categoryLocaleTags)->forget(CacheKeys::categoryNavigationTree());
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

        // Update the navigation tree version marker so cached category trees
        // that rely on optimistic locking automatically refresh downstream.
        $this->bumpVersion(self::TREE_VERSION_KEY);
    }

    private function bumpVersion(string $key): void
    {
        // UUID payloads prevent cache stampedes by ensuring each invalidation
        // cycle produces a unique token for downstream array-based stores.
        Cache::forever($key, Str::uuid()->toString());
    }
}
