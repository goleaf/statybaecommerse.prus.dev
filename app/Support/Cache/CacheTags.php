<?php

declare(strict_types=1);

namespace App\Support\Cache;

/**
 * Centralised cache tag helper used across the storefront and dashboard.
 *
 * The helper keeps tag values consistent so that cache invalidation actions
 * (for example the Filament cache maintenance screen) can flush partial caches
 * without guessing tag names.
 */
final class CacheTags
{
    public static function home(): string
    {
        return 'home';
    }

    public static function dashboard(): string
    {
        return 'dashboard';
    }

    public static function locale(string $locale): string
    {
        return sprintf('locale:%s', $locale);
    }

    public static function products(): string
    {
        return 'products';
    }

    /**
     * @param  array<int, int|string> $ids
     * @return array<int, string>
     */
    public static function productIds(array $ids): array
    {
        return self::mapIds('product', $ids);
    }

    public static function categories(): string
    {
        return 'categories';
    }

    /**
     * @param  array<int, int|string> $ids
     * @return array<int, string>
     */
    public static function categoryIds(array $ids): array
    {
        return self::mapIds('category', $ids);
    }

    public static function category(int $id): string
    {
        return sprintf('category:%d', $id);
    }

    public static function brands(): string
    {
        return 'brands';
    }

    public static function currencies(): string
    {
        return 'currencies';
    }

    /**
     * @param  array<int, int|string> $ids
     * @return array<int, string>
     */
    public static function brandIds(array $ids): array
    {
        return self::mapIds('brand', $ids);
    }

    public static function brand(int $id): string
    {
        return sprintf('brand:%d', $id);
    }

    public static function collections(): string
    {
        return 'collections';
    }

    public static function sliders(): string
    {
        // Shared tag identifier for storefront slider payloads.
        return 'sliders';
    }

    /**
     * @param  array<int, int|string> $ids
     * @return array<int, string>
     */
    public static function collectionIds(array $ids): array
    {
        return self::mapIds('collection', $ids);
    }

    public static function reviews(): string
    {
        return 'reviews';
    }

    public static function settings(): string
    {
        return 'settings';
    }

    public static function testing(): string
    {
        return 'testing';
    }

    public static function orders(): string
    {
        return 'orders';
    }

    public static function users(): string
    {
        return 'users';
    }

    /**
     * Collapse identifiers into deterministic tag names while stripping
     * duplicates to avoid redundant cache tagging.
     *
     * @param  array<int, int|string> $ids
     * @return array<int, string>
     */
    private static function mapIds(string $prefix, array $ids): array
    {
        $ids = array_map(static fn ($id): int => (int) $id, $ids);
        $ids = array_values(array_unique($ids));

        return array_map(static fn (int $id): string => sprintf('%s:%d', $prefix, $id), $ids);
    }
}
