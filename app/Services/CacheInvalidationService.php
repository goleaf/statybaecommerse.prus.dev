<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CacheInvalidationService
{
    /**
     * Map of model classes to their associated cache tags.
     *
     * @var array<class-string, array<int, string>>
     */
    private const MODEL_TAGS = [
        Product::class => [
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::CATEGORIES,
            CacheTagHelper::BRANDS,
            CacheTagHelper::COLLECTIONS,
            CacheTagHelper::DASHBOARDS,
        ],
        Category::class => [
            CacheTagHelper::CATEGORIES,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
        Brand::class => [
            CacheTagHelper::BRANDS,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
        Collection::class => [
            CacheTagHelper::COLLECTIONS,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
    ];

    /**
     * Flush cache tags for a specific model instance.
     */
    public function flushForModel(Model $model): void
    {
        foreach (self::MODEL_TAGS as $class => $tags) {
            if ($model instanceof $class) {
                $this->flushTags($tags);
            }
        }
    }

    public function flushProducts(): void
    {
        $this->flushTags(CacheTagHelper::products());
    }

    public function flushCategories(): void
    {
        $this->flushTags(CacheTagHelper::categories());
    }

    public function flushBrands(): void
    {
        $this->flushTags(CacheTagHelper::brands());
    }

    public function flushCollections(): void
    {
        $this->flushTags(CacheTagHelper::collections());
    }

    public function flushDashboards(): void
    {
        $this->flushTags(CacheTagHelper::dashboards());
    }

    /**
     * Attempt to flush the given tags and gracefully handle unsupported stores.
     *
     * @param  array<int, string>  $tags
     */
    private function flushTags(array $tags): void
    {
        if ($tags === []) {
            return;
        }

        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            try {
                Cache::tags($tags)->flush();

                return;
            } catch (Throwable $exception) {
                Log::warning('Failed to flush cache tags', [
                    'tags' => $tags,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Cache::flush();
    }
}
