<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class InvalidateCategoryCache
{
    use ResolvesSupportedLocales;

    public function __invoke(?int $categoryId = null): void
    {
        if (Cache::supportsTags()) {
            $tags = [CacheKeys::navigationTag(), CacheKeys::homeTag()];

            if ($categoryId !== null) {
                $tags[] = CacheKeys::categoryTag($categoryId);
            }

            Cache::tags($tags)->flush();

            return;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));
            Cache::forget(CacheKeys::navigationCategories(8, $locale));
        }

        Log::debug('Category caches invalidated via fallback path.', [
            'category_id' => $categoryId,
        ]);
    }
}
