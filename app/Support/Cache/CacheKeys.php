<?php

declare(strict_types=1);

namespace App\Support\Cache;

use JsonException;

final class CacheKeys
{
    public const TTL_MINUTE = 60;

    public const TTL_TWO_MINUTES = 120;

    public const TTL_FIVE_MINUTES = 300;

    public const TTL_ONE_HOUR = 3600;

    public const TTL_TWO_HOURS = 7200;

    public const TTL_SIX_HOURS = 21600;

    public const TTL_ONE_DAY = 86400;

    public static function productVisibleCount(): string
    {
        return 'product:visible:count';
    }

    public static function productTotalCount(): string
    {
        return 'product:aggregate:count';
    }

    public static function userTotalCount(): string
    {
        return 'user:aggregate:count';
    }

    public static function dashboardMetric(string $metric, string $locale): string
    {
        return sprintf('dashboard:metrics:%s:%s', $metric, $locale);
    }

    public static function productAggregateTag(): string
    {
        return 'product:aggregate';
    }

    public static function userAggregateTag(): string
    {
        return 'user:aggregate';
    }

    /**
     * Build the cache key for customer order activity sparklines.
     */
    public static function customerOrdersSeries(int $customerId, int $days): string
    {
        return sprintf('customer:series:orders:%d:%d', $customerId, $days);
    }

    public static function orderAggregateTag(): string
    {
        return 'order:aggregate';
    }

    public static function homeStats(string $locale): string
    {
        return sprintf('home:stats:%s', $locale);
    }

    public static function homeFeaturedProducts(string $locale): string
    {
        return sprintf('home:featured:%s', $locale);
    }

    public static function homeLatestProducts(string $locale): string
    {
        return sprintf('home:latest-products:%s', $locale);
    }

    public static function homeLatestReviews(string $locale): string
    {
        return sprintf('home:latest-reviews:%s', $locale);
    }

    public static function dashboardSimplifiedSummary(): string
    {
        return 'dashboard:simplified-stats:summary';
    }

    public static function dashboardSimplifiedChart(string $startDate, string $endDate): string
    {
        return sprintf('dashboard:simplified-stats:chart:%s:%s', $startDate, $endDate);
    }

    public static function categoryIndexBrands(string $locale): string
    {
        return sprintf('category:index:brands:%s', $locale);
    }

    public static function categoryIndexCollections(string $locale): string
    {
        return sprintf('category:index:collections:%s', $locale);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function categoryIndexFacetBrands(string $locale, array $filters): string
    {
        return sprintf('category:index:facet-brands:%s:%s', $locale, self::hashFromArray($filters));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function categoryIndexFacetCollections(string $locale, array $filters): string
    {
        return sprintf('category:index:facet-collections:%s:%s', $locale, self::hashFromArray($filters));
    }

    public static function productLatestList(int $limit): string
    {
        return sprintf('product:latest:list:%d', $limit);
    }

    /**
     * Build the cache key for sparkline-ready product sales series.
     */
    public static function productSalesSeries(int $productId, int $days): string
    {
        return sprintf('product:series:sales:%d:%d', $productId, $days);
    }

    public static function categoryPopularList(int $limit): string
    {
        return sprintf('category:index:facet-categories:%s:%s', $locale, self::hashFromArray($filters));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function categoryIndexCategories(string $locale, array $filters): string
    {
        return sprintf('category:index:categories:%s:%s', $locale, self::hashFromArray($filters));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public static function categoryShowProducts(int $categoryId, string $locale, array $options): string
    {
        return sprintf('category:show:%d:products:%s:%s', $categoryId, $locale, self::hashFromArray($options));
    }

    public static function navigationCategories(int $limit, string $locale): string
    {
        return sprintf('navigation:categories:%d:%s', $limit, $locale);
    }

    public static function menuCollectionKey(?string $location, string $locale): string
    {
        return sprintf('menu:collection:%s:%s', $location ?? 'all', $locale);
    }

    public static function menuByKey(string $key, string $locale): string
    {
        return sprintf('menu:key:%s:%s', $key, $locale);
    }

    public static function menuByLocation(string $location, string $locale): string
    {
        return sprintf('menu:location:%s:%s', $location, $locale);
    }

    public static function dashboardStats(string $range): string
    {
        return sprintf('product:detail:%d:%s', $productId, $locale);
    }

    public static function productRecentHistories(int $productId): string
    {
        return sprintf('product:%d:recent-histories', $productId);
    }

    public static function productRecentReviews(int $productId): string
    {
        return sprintf('product:%d:recent-reviews', $productId);
    }

    /**
     * @param  array<mixed>  $values
     *
     * @throws JsonException
     */
    private static function encodeArray(array $values): string
    {
        return json_encode($values, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<mixed>  $values
     */
    private static function hashFromArray(array $values): string
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

    public static function menuTag(int $menuId): string
    {
        return sprintf('menu:%d', $menuId);
    }

    public static function brandTag(int $brandId): string
    {
        return sprintf('brand:%d', $brandId);
    }

    public static function homeTag(): string
    {
        return 'home';
    }

    public static function navigationTag(): string
    {
        return 'navigation';
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
