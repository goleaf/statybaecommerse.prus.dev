<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

/**
 * @covers \App\Services\CacheInvalidationService
 */
final class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function test_flush_dashboards_clears_known_keys(): void
    {
        Cache::put(CacheKeys::dashboardStats('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardActivity('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardPerformance('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardSimplifiedSummary(), ['value' => 1], 600);

        app(CacheInvalidationService::class)->flushDashboards();

        $this->assertFalse(Cache::has(CacheKeys::dashboardStats('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardActivity('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardPerformance('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardSimplifiedSummary()));
    }

    public function test_flush_products_forgets_featured_lists_on_array_store(): void
    {
        Cache::put(CacheKeys::productFeaturedList(8), ['value' => 1], 600);
        Cache::put(CacheKeys::productLatestList(6), ['value' => 1], 600);

        app(CacheInvalidationService::class)->flushProducts();

        $this->assertFalse(Cache::has(CacheKeys::productFeaturedList(8)));
        $this->assertFalse(Cache::has(CacheKeys::productLatestList(6)));
    }
}
