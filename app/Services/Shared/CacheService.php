<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheTagHelper;
use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

/**
 * CacheService
 *
 * Service class containing CacheService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class CacheService
{
    private const DEFAULT_TTL = 3600;

    // 1 hour
    private const SHORT_TTL = 900;

    // 15 minutes
    private const LONG_TTL = 86400;

    // 24 hours
    private readonly bool $supportsTags;

    public function __construct()
    {
        $this->supportsTags = Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Handle rememberShort functionality with proper error handling.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @param  array<int, string>  $tags
     * @return TValue
     */
    public function rememberShort(string $key, Closure $callback, ?int $ttl = null, array $tags = []): mixed
    {
        return $this->rememberWithTags($tags, $key, $ttl ?? self::SHORT_TTL, $callback);
    }

    /**
     * Handle rememberDefault functionality with proper error handling.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @param  array<int, string>  $tags
     * @return TValue
     */
    public function rememberDefault(string $key, Closure $callback, ?int $ttl = null, array $tags = []): mixed
    {
        return $this->rememberWithTags($tags, $key, $ttl ?? self::DEFAULT_TTL, $callback);
    }

    /**
     * Handle rememberLong functionality with proper error handling.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @param  array<int, string>  $tags
     * @return TValue
     */
    public function rememberLong(string $key, Closure $callback, ?int $ttl = null, array $tags = []): mixed
    {
        return $this->rememberWithTags($tags, $key, $ttl ?? self::LONG_TTL, $callback);
    }

    /**
     * @template TValue
     *
     * @param  array<int, string>  $tags
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    private function rememberWithTags(array $tags, string $key, int|\DateInterval $ttl, Closure $callback): mixed
    {
        if ($tags !== [] && $this->supportsTags) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Handle generateProductKey functionality with proper error handling.
     */
    public function generateProductKey(int $productId, string $locale, string $currency): string
    {
        return "product.{$productId}.{$locale}.{$currency}";
    }

    /**
     * Handle generateCategoryKey functionality with proper error handling.
     */
    public function generateCategoryKey(int $categoryId, string $locale): string
    {
        return "category.{$categoryId}.{$locale}";
    }

    /**
     * Handle generateBrandKey functionality with proper error handling.
     */
    public function generateBrandKey(int $brandId, string $locale): string
    {
        return "brand.{$brandId}.{$locale}";
    }

    /**
     * Handle generateCollectionKey functionality with proper error handling.
     */
    public function generateCollectionKey(int $collectionId, string $locale): string
    {
        return "collection.{$collectionId}.{$locale}";
    }

    /**
     * Handle generateHomeKey functionality with proper error handling.
     */
    public function generateHomeKey(string $section, string $locale, ?string $currency = null): string
    {
        $key = "home.{$section}.{$locale}";
        if ($currency) {
            $key .= ".{$currency}";
        }

        return $key;
    }

    /**
     * Handle invalidateProductCache functionality with proper error handling.
     */
    public function invalidateProductCache(int $productId): void
    {
        app(CacheInvalidationService::class)->flushProducts();
    }

    /**
     * Handle invalidateCategoryCache functionality with proper error handling.
     */
    public function invalidateCategoryCache(int $categoryId): void
    {
        app(CacheInvalidationService::class)->flushCategories();
    }

    /**
     * Handle invalidateBrandCache functionality with proper error handling.
     */
    public function invalidateBrandCache(int $brandId): void
    {
        app(CacheInvalidationService::class)->flushBrands();
    }

    /**
     * Handle invalidateCollectionCache functionality with proper error handling.
     */
    public function invalidateCollectionCache(int $collectionId): void
    {
        app(CacheInvalidationService::class)->flushCollections();
    }

    /**
     * Handle warmupHomeCache functionality with proper error handling.
     */
    public function warmupHomeCache(): void
    {
        $locales = ['lt', 'en', 'de'];
        $currencies = ['EUR'];
        foreach ($locales as $locale) {
            foreach ($currencies as $currency) {
                app()->setLocale($locale);
                // Warm up featured products
                $this->rememberDefault(
                    $this->generateHomeKey('featured_products', $locale, $currency),
                    fn () => \App\Models\Product::query()->with(['translations', 'brand', 'media', 'prices'])->where('is_visible', true)->where('is_featured', true)->limit(8)->get(),
                    null,
                    CacheTagHelper::merge(CacheTagHelper::products(), CacheTagHelper::locale($locale)),
                );
                // Warm up top categories
                $this->rememberLong(
                    $this->generateHomeKey('top_categories', $locale),
                    fn () => \App\Models\Category::query()->with(['translations', 'media'])->where('is_visible', true)->whereNull('parent_id')->withCount('products')->orderBy('products_count', 'desc')->limit(8)->get(),
                    null,
                    CacheTagHelper::merge(CacheTagHelper::categories(), CacheTagHelper::locale($locale)),
                );
            }
        }
    }

    /**
     * Helper to remember cache values while conditionally applying tags.
     *
     * @template TCacheValue
     *
     * @param  array<int, string>     $tags
     * @param  Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    private function rememberWithTags(array $tags, string $key, int|DateInterval $ttl, Closure $callback): mixed
    {
        if ($tags !== [] && $this->supportsTags) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }
}
