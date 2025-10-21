<?php

declare(strict_types=1);

namespace App\Support\Cache;

/**
 * Helper utility that centralizes cache tag groups for catalog features.
 */
final class CacheTagHelper
{
    /**
     * Tags used for product centric caches (lists, shelves, aggregates).
     *
     * @return array<int, string>
     */
    public static function products(): array
    {
        return self::unique([
            'products',
            CacheKeys::productAggregateTag(),
            CacheKeys::homeTag(),
            CacheKeys::dashboardTag(),
            CacheKeys::navigationTag(),
        ]);
    }

    /**
     * Tags used for category centric caches (navigation, filters, counts).
     *
     * @return array<int, string>
     */
    public static function categories(): array
    {
        return self::unique([
            'categories',
            CacheKeys::navigationTag(),
            CacheKeys::homeTag(),
        ]);
    }

    /**
     * Tags used for brand centric caches.
     *
     * @return array<int, string>
     */
    public static function brands(): array
    {
        return self::unique([
            'brands',
            CacheKeys::navigationTag(),
            CacheKeys::homeTag(),
        ]);
    }

    /**
     * Tags used for collection centric caches.
     *
     * @return array<int, string>
     */
    public static function collections(): array
    {
        return self::unique([
            'collections',
            CacheKeys::homeTag(),
        ]);
    }

    /**
     * Tags used for dashboard/statistical caches.
     *
     * @return array<int, string>
     */
    public static function dashboards(): array
    {
        return self::unique([
            'dashboards',
            CacheKeys::dashboardTag(),
        ]);
    }

    /**
     * Merge multiple tag groups while preventing duplicates or empty values.
     *
     * @param  array<int, string> ...$groups
     * @return array<int, string>
     */
    public static function merge(array ...$groups): array
    {
        return self::unique(array_merge(...$groups));
    }

    /**
     * Normalize a set of tags by filtering empties and removing duplicates.
     *
     * @param  array<int, string> $tags
     * @return array<int, string>
     */
    private static function unique(array $tags): array
    {
        return array_values(array_unique(array_filter($tags, static fn ($tag) => filled($tag))));
    }
}
