<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central service that coordinates cache invalidation for catalog driven features.
 */
final class CacheInvalidationService
{
    /**
     * Flush cache tags that correspond to the given model instance.
     */
    public function flushForModel(Model $model): void
    {
        $tags = $this->tagsForModel($model);

        if ($tags !== []) {
            $this->flushTags($tags);
        }
    }

    /**
     * Force refresh of all product centric caches.
     */
    public function flushProducts(): void
    {
        $this->flushTags(CacheTagHelper::products());
    }

    /**
     * Force refresh of all category centric caches.
     */
    public function flushCategories(): void
    {
        $this->flushTags(CacheTagHelper::categories());
    }

    /**
     * Force refresh of all brand centric caches.
     */
    public function flushBrands(): void
    {
        $this->flushTags(CacheTagHelper::brands());
    }

    /**
     * Force refresh of collection centric caches.
     */
    public function flushCollections(): void
    {
        $this->flushTags(CacheTagHelper::collections());
    }

    /**
     * Force refresh of dashboard caches.
     */
    public function flushDashboards(): void
    {
        $this->flushTags(CacheTagHelper::dashboards());
    }

    /**
     * Determine which tag groups should be cleared for a model instance.
     *
     * @return array<int, string>
     */
    private function tagsForModel(Model $model): array
    {
        if ($model instanceof Product) {
            return CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::categories(),
                CacheTagHelper::brands(),
                CacheTagHelper::collections(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Category) {
            return CacheTagHelper::merge(
                CacheTagHelper::categories(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Brand) {
            return CacheTagHelper::merge(
                CacheTagHelper::brands(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Collection) {
            return CacheTagHelper::merge(
                CacheTagHelper::collections(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        return [];
    }

    /**
     * Attempt to flush a set of tags with graceful degradation when unsupported.
     *
     * @param array<int, string> $tags
     */
    private function flushTags(array $tags): void
    {
        if ($tags === []) {
            return;
        }

        if (Cache::supportsTags()) {
            try {
                Cache::tags($tags)->flush();

                return;
            } catch (Throwable $exception) {
                Log::warning('Failed to flush cache tags', [
                    'tags'  => $tags,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Cache::flush();
    }
}
