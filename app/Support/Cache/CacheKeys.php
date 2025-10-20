<?php

declare(strict_types=1);

namespace App\Support\Cache;

final class CacheKeys
{
    public const TTL_MINUTE = 60;

    public const TTL_TWO_MINUTES = 120;

    public const TTL_FIVE_MINUTES = 300;

    public const TTL_ONE_HOUR = 3600;

    public const TTL_TWO_HOURS = 7200;

    public const TTL_SIX_HOURS = 21600;

    public const TTL_ONE_DAY = 86400;

    public static function homeStats(string $locale): string
    {
        return self::homeKey('stats', $locale);
    }

    public static function homeFeaturedProducts(string $locale): string
    {
        return self::homeKey('featured', $locale);
    }

    public static function homeLatestProducts(string $locale): string
    {
        return self::homeKey('latest-products', $locale);
    }

    public static function homeLatestReviews(string $locale): string
    {
        return self::homeKey('latest-reviews', $locale);
    }

    public static function homeShelf(string $preset, int $limit, string $locale): string
    {
        return sprintf('home:shelf:%s:%d:%s', $preset, $limit, $locale);
    }

    public static function homeCollections(string $locale): string
    {
        return self::homeKey('collections', $locale);
    }

    public static function homeSliders(string $locale): string
    {
        return self::homeKey('sliders', $locale);
    }

    public static function homeCategoryTree(string $locale): string
    {
        return self::homeKey('category-tree', $locale);
    }

    public static function homeCatalogueCategories(string $locale): string
    {
        return self::homeKey('catalogue:categories', $locale);
    }

    public static function productFeaturedList(int $limit): string
    {
        return sprintf('product:featured:list:%d', $limit);
    }

    public static function categoryPopularList(int $limit): string
    {
        return sprintf('category:popular:list:%d', $limit);
    }

    public static function brandTopList(int $limit): string
    {
        return sprintf('brand:top:list:%d', $limit);
    }

    public static function categoryNavigationTree(): string
    {
        return 'category:navigation:tree';
    }

    public static function dashboardStats(string $range): string
    {
        return sprintf('dashboard:live:stats:%s', $range);
    }

    public static function dashboardActivity(string $range): string
    {
        return sprintf('dashboard:live:activity:%s', $range);
    }

    public static function dashboardPerformance(string $range): string
    {
        return sprintf('dashboard:live:performance:%s', $range);
    }

    public static function dashboardSummary(): string
    {
        return 'dashboard:simplified:summary';
    }

    public static function currencyEnabledList(): string
    {
        return 'currency:enabled:list';
    }

    public static function currencyDefaultCode(): string
    {
        return 'currency:default:code';
    }

    public static function productTag(int $productId): string
    {
        return sprintf('product:%d', $productId);
    }

    public static function categoryTag(int $categoryId): string
    {
        return sprintf('category:%d', $categoryId);
    }

    public static function brandTag(int $brandId): string
    {
        return sprintf('brand:%d', $brandId);
    }

    public static function homeTag(): string
    {
        return 'home';
    }

    public static function dashboardTag(): string
    {
        return 'dashboard';
    }

    private static function homeKey(string $segment, string $locale): string
    {
        return sprintf('home:%s:%s', $segment, $locale);
    }
}
