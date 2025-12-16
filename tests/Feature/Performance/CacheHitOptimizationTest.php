<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Support\Cache\CacheWarmer;
use App\Support\Localization\LocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * **Feature: performance-update, Property 6: Cache Hit Optimization**
 * **Validates: Requirements 4.3**
 *
 * Ensures zero database queries when caches are warm and prevents redundant cache writes.
 */
final class CacheHitOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_caches_require_zero_database_queries(): void
    {
        $warmer = app(CacheWarmer::class);
        $localeResolver = app(LocaleResolver::class);
        
        // Warm caches first
        $warmer->warmStorefront();
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Test that warm caches don't trigger queries
        foreach ($localeResolver->getSupportedLocales() as $locale) {
            expect($warmer->areCachesWarm($locale))->toBeTrue();
        }

        expect($queryCount)->toBe(0, 'Checking warm cache status should not trigger database queries');
    }

    public function test_cache_warming_populates_critical_keys(): void
    {
        Cache::flush();
        
        $warmer = app(CacheWarmer::class);
        $localeResolver = app(LocaleResolver::class);
        
        // Verify caches are cold
        foreach ($localeResolver->getSupportedLocales() as $locale) {
            expect($warmer->areCachesWarm($locale))->toBeFalse();
        }
        
        // Warm caches
        $warmer->warmStorefront();
        
        // Verify caches are now warm
        foreach ($localeResolver->getSupportedLocales() as $locale) {
            expect($warmer->areCachesWarm($locale))->toBeTrue();
        }
    }

    public function test_redundant_cache_writes_are_prevented(): void
    {
        $warmer = app(CacheWarmer::class);
        
        $testData = ['test' => 'data', 'timestamp' => now()->timestamp];
        $cacheKey = 'test_redundancy_key';
        
        // First write should be allowed
        Cache::put($cacheKey, $testData, 3600);
        expect($warmer->shouldUpdateCache($cacheKey, $testData))->toBeFalse();
        
        // Different data should trigger update
        $newData = ['test' => 'different', 'timestamp' => now()->timestamp];
        expect($warmer->shouldUpdateCache($cacheKey, $newData))->toBeTrue();
        
        // Non-existent key should trigger update
        expect($warmer->shouldUpdateCache('non_existent_key', $testData))->toBeTrue();
    }

    public function test_cache_warming_handles_missing_data_gracefully(): void
    {
        Cache::flush();
        
        // Clear all test data to simulate empty database
        DB::table('products')->delete();
        DB::table('brands')->delete();
        DB::table('categories')->delete();
        DB::table('collections')->delete();
        
        $warmer = app(CacheWarmer::class);
        
        // Should not throw exceptions even with empty database
        expect(fn () => $warmer->warmStorefront())->not->toThrow();
    }

    public function test_cache_hit_optimization_with_tags(): void
    {
        if (!Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
            $this->markTestSkipped('Cache store does not support tags');
        }

        Cache::flush();
        
        $warmer = app(CacheWarmer::class);
        
        // Warm caches with tags
        $warmer->warmStorefront();
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Access cached data - should not trigger queries
        $featuredProducts = Cache::tags(['products', 'home'])->get('collection:featured_products.lt');
        $featuredBrands = Cache::tags(['brands', 'home'])->get('collection:featured_brands.lt');
        
        expect($featuredProducts)->not->toBeNull()
            ->and($featuredBrands)->not->toBeNull()
            ->and($queryCount)->toBe(0);
    }

    public function test_cache_warming_respects_locale_specific_data(): void
    {
        Cache::flush();
        
        $warmer = app(CacheWarmer::class);
        $localeResolver = app(LocaleResolver::class);
        
        $warmer->warmStorefront();
        
        foreach ($localeResolver->getSupportedLocales() as $locale) {
            $featuredProducts = Cache::get("collection:featured_products.{$locale}");
            $navigationCategories = Cache::get("collection:navigation_categories.{$locale}");
            
            expect($featuredProducts)->not->toBeNull("Featured products should be cached for locale {$locale}")
                ->and($navigationCategories)->not->toBeNull("Navigation categories should be cached for locale {$locale}");
        }
    }
}