<?php

declare(strict_types=1);

namespace App\Support\Cache;

use JsonException;

final class CacheKeys
{
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

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function categoryIndexFacetCategories(string $locale, array $filters): string
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

    public static function productDetail(int $productId, string $locale): string
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
        try {
            return substr(sha1(self::encodeArray($values)), 0, 16);
        } catch (JsonException) {
            return substr(sha1(serialize($values)), 0, 16);
        }
    }
}
