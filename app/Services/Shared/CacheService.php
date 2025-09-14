<?php

declare(strict_types=1);

namespace App\Services\Shared;

use Illuminate\Support\Facades\Cache;

final /**
 * CacheService
 * 
 * Service class containing business logic and external integrations.
 */
class CacheService
{
    private const DEFAULT_TTL = 3600; // 1 hour

    private const SHORT_TTL = 900; // 15 minutes

    private const LONG_TTL = 86400; // 24 hours

    public function rememberShort(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? self::SHORT_TTL, $callback);
    }

    public function rememberDefault(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? self::DEFAULT_TTL, $callback);
    }

    public function rememberLong(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? self::LONG_TTL, $callback);
    }

    public function forgetPattern(string $pattern): void
    {
        $keys = Cache::getRedis()->keys($pattern);
        if (! empty($keys)) {
            Cache::deleteMultiple($keys);
        }
    }

    public function generateProductKey(int $productId, string $locale, string $currency): string
    {
        return "product.{$productId}.{$locale}.{$currency}";
    }

    public function generateCategoryKey(int $categoryId, string $locale): string
    {
        return "category.{$categoryId}.{$locale}";
    }

    public function generateBrandKey(int $brandId, string $locale): string
    {
        return "brand.{$brandId}.{$locale}";
    }

    public function generateCollectionKey(int $collectionId, string $locale): string
    {
        return "collection.{$collectionId}.{$locale}";
    }

    public function generateHomeKey(string $section, string $locale, ?string $currency = null): string
    {
        $key = "home.{$section}.{$locale}";
        if ($currency) {
            $key .= ".{$currency}";
        }

        return $key;
    }

    public function invalidateProductCache(int $productId): void
    {
        $this->forgetPattern("product.{$productId}.*");
        $this->forgetPattern('home.*'); // Home page caches products
        $this->forgetPattern('category.*'); // Categories cache product counts
        $this->forgetPattern('brand.*'); // Brands cache product counts
    }

    public function invalidateCategoryCache(int $categoryId): void
    {
        $this->forgetPattern("category.{$categoryId}.*");
        $this->forgetPattern('home.top_categories.*');
    }

    public function invalidateBrandCache(int $brandId): void
    {
        $this->forgetPattern("brand.{$brandId}.*");
        $this->forgetPattern('home.top_brands.*');
    }

    public function invalidateCollectionCache(int $collectionId): void
    {
        $this->forgetPattern("collection.{$collectionId}.*");
        $this->forgetPattern('home.featured_collections.*');
    }

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
                    fn () => \App\Models\Product::query()
                        ->with(['translations', 'brand', 'media', 'prices'])
                        ->where('is_visible', true)
                        ->where('is_featured', true)
                        ->limit(8)
                        ->get()
                );

                // Warm up top categories
                $this->rememberLong(
                    $this->generateHomeKey('top_categories', $locale),
                    fn () => \App\Models\Category::query()
                        ->with(['translations', 'media'])
                        ->where('is_visible', true)
                        ->whereNull('parent_id')
                        ->withCount('products')
                        ->orderBy('products_count', 'desc')
                        ->limit(8)
                        ->get()
                );
            }
        }
    }
}
