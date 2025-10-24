<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CategoryRepository
{
    public function navigation(int $limit = 8, ?string $locale = null): Collection
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $cacheKey = CacheKeys::navigationCategories($limit, $resolvedLocale);

        $callback = static function () use ($limit, $resolvedLocale): Collection {
            $categories = Category::query()
                ->topLevelVisible()
                ->withLocale($resolvedLocale)
                ->with([
                    'children' => static fn ($query) => $query
                        ->visible()
                        ->ordered()
                        ->withLocale($resolvedLocale)
                        ->limit(5),
                ])
                ->limit($limit)
                ->get();

            return $categories->map(static function (Category $category): array {
                return [
                    'id' => $category->id,
                    'name' => $category->getTranslatedName(),
                    'slug' => $category->slug,
                    'url' => route('categories.show', $category->slug),
                    'icon' => $category->icon,
                    'children' => $category->children->map(static function (Category $child): array {
                        return [
                            'id' => $child->id,
                            'name' => $child->getTranslatedName(),
                            'slug' => $child->slug,
                            'url' => route('categories.show', $child->slug),
                        ];
                    })->all(),
                ];
            });
        };

        // Merge category and locale tags so navigation caches respect the
        // global invalidation service when catalogue data shifts.
        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($resolvedLocale)
        );

        return $this->remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, $callback, $tags);
    }

    /**
     * @param  array<int, string>  $tags
     */
    private function remember(string $key, int $ttlSeconds, callable $callback, array $tags = []): Collection
    {
        $expiresAt = now()->addSeconds($ttlSeconds);

        if ($tags !== [] && CacheTagHelper::supportsTags()) {
            return Cache::tags($tags)->remember($key, $expiresAt, $callback);
        }

        return Cache::remember($key, $expiresAt, $callback);
    }

    public function getVisibleTree(?string $locale = null): Collection
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $supportsTags = CacheTagHelper::supportsTags();
        $cacheKey = CacheKeys::categoryNavigationTree();

        if (! $supportsTags) {
            $cacheKey .= ':'.$resolvedLocale;
        }

        $callback = static function () use ($resolvedLocale): Collection {
            return Category::query()
                ->topLevelVisible()
                ->withLocale($resolvedLocale)
                ->withCount('products')
                ->with([
                    'children' => static fn ($query) => $query
                        ->visible()
                        ->ordered()
                        ->withLocale($resolvedLocale)
                        ->withCount('products'),
                ])
                ->ordered()
                ->get();
        };

        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($resolvedLocale)
        );

        return $this->remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, $callback, $supportsTags ? $tags : []);
    }
}
