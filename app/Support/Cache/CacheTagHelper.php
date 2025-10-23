<?php

declare(strict_types=1);

namespace App\Support\Cache;

/**
 * Helper providing cache tag constants and utility methods.
 */
final class CacheTagHelper
{
    public const PRODUCTS = 'products';

    public const CATEGORIES = 'categories';

    public const BRANDS = 'brands';

    public const COLLECTIONS = 'collections';

    public const DASHBOARDS = 'dashboards';

    /**
     * Tags used for product related caches.
     *
     * @return array<int, string>
     */
    public static function products(): array
    {
        return [self::PRODUCTS];
    }

    /**
     * Tags used for category related caches.
     *
     * @return array<int, string>
     */
    public static function categories(): array
    {
        return [self::CATEGORIES];
    }

    /**
     * Tags used for brand related caches.
     *
     * @return array<int, string>
     */
    public static function brands(): array
    {
        return [self::BRANDS];
    }

    /**
     * Tags used for collection related caches.
     *
     * @return array<int, string>
     */
    public static function collections(): array
    {
        return [self::COLLECTIONS];
    }

    /**
     * Tags used for dashboard/statistics caches.
     *
     * @return array<int, string>
     */
    public static function dashboards(): array
    {
        return [self::DASHBOARDS];
    }

    /**
     * Merge multiple tag groups, removing duplicates.
     *
     * @param  array<int, string> ...$groups
     * @return array<int, string>
     */
    public static function merge(array ...$groups): array
    {
        if ($groups === []) {
            // Provide a graceful fallback when no tag groups are supplied.
            return [];
        }

        return array_values(array_unique(array_merge(...$groups)));
    }
}
