<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

/**
 * Helper for assembling cache tag groups in a consistent manner.
 *
 * Filament panels and Livewire components both rely on deterministic tag
 * values so that invalidation routines (and the upcoming maintenance widgets)
 * can flush scoped caches without resorting to broad `Cache::flush()` calls.
 */
final class CacheTagHelper
{
    /**
     * Shared tag identifiers used across the storefront and dashboard layers.
     */
    public const PRODUCTS = 'products';

    public const CATEGORIES = 'categories';

    public const BRANDS = 'brands';

    public const COLLECTIONS = 'collections';

    public const DASHBOARDS = 'dashboard';

    /**
     * @return array<int, string>
     */
    public static function products(): array
    {
        return [CacheTags::products()];
    }

    /**
     * @return array<int, string>
     */
    public static function categories(): array
    {
        return [CacheTags::categories()];
    }

    /**
     * @return array<int, string>
     */
    public static function brands(): array
    {
        return [CacheTags::brands()];
    }

    /**
     * @return array<int, string>
     */
    public static function collections(): array
    {
        return [CacheTags::collections()];
    }

    /**
     * @return array<int, string>
     */
    public static function dashboards(): array
    {
        return [CacheTags::dashboard()];
    }

    /**
     * Tag helper for locale-aware caches such as the storefront widgets.
     *
     * @return array<int, string>
     */
    public static function locale(string $locale): array
    {
        return [CacheTags::locale($locale)];
    }

    /**
     * Merge multiple tag groups, removing duplicates to keep the payload lean.
     *
     * @param  array<int, string> ...$groups
     * @return array<int, string>
     */
    public static function merge(array ...$groups): array
    {
        if ($groups === []) {
            return [];
        }

        return array_values(array_unique(array_merge(...$groups)));
    }

    /**
     * Determine if the underlying cache store supports tag operations.
     */
    public static function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }
}
