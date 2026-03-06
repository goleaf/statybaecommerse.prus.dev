<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Dashboard\DashboardMetricsRepository;
use App\Support\Cache\CacheKeys;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class DashboardMetricsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardMetricsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DashboardMetricsRepository;

        // Clear cache before each test
        Cache::flush();

        // Remove seeded orders so each assertion only evaluates records created in this test.
        Order::query()->withoutGlobalScopes()->delete();

        // Set test time
        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));
    }

    public function test_orders_today_returns_correct_count(): void
    {
        // Create orders for today
        Order::factory()->count(3)->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
        ]);

        // Create orders for yesterday (should not be counted)
        Order::factory()->count(2)->create([
            'created_at' => CarbonImmutable::now()->subDay(),
        ]);

        $result = $this->repository->ordersToday();

        $this->assertEquals(3, $result);
    }

    public function test_orders_today_excludes_deleted_orders(): void
    {
        // Create active orders
        Order::factory()->count(2)->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
            'deleted_at' => null,
        ]);

        // Create soft-deleted orders
        Order::factory()->count(1)->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
            'deleted_at' => CarbonImmutable::now(),
        ]);

        $result = $this->repository->ordersToday();

        $this->assertEquals(2, $result);
    }

    public function test_revenue_last_seven_days_calculates_correctly(): void
    {
        // Set revenue statuses in config
        Config::set('dashboard.revenue_statuses', ['completed', 'delivered']);

        // Create orders within the last 7 days with revenue statuses
        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(2),
            'status'     => 'completed',
            'total'      => 100.50,
        ]);

        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(5),
            'status'     => 'delivered',
            'total'      => 250.75,
        ]);

        // Create order with non-revenue status (should not be counted)
        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(3),
            'status'     => 'pending',
            'total'      => 50.00,
        ]);

        // Create order older than 7 days (should not be counted)
        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(8),
            'status'     => 'completed',
            'total'      => 75.25,
        ]);

        $result = $this->repository->revenueLastSevenDays();

        $this->assertEquals(351.25, $result);
    }

    public function test_revenue_includes_all_statuses_when_config_empty(): void
    {
        Config::set('dashboard.revenue_statuses', []);

        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(2),
            'status'     => 'pending',
            'total'      => 100.00,
        ]);

        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->subDays(3),
            'status'     => 'cancelled',
            'total'      => 50.00,
        ]);

        $result = $this->repository->revenueLastSevenDays();

        $this->assertEquals(150.00, $result);
    }

    public function test_new_users_today_counts_non_admin_users(): void
    {
        // Create regular users today
        User::factory()->count(2)->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(3),
            'is_admin'   => false,
        ]);

        // Create admin user today (should not be counted)
        User::factory()->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(4),
            'is_admin'   => true,
        ]);

        // Create user yesterday (should not be counted)
        User::factory()->create([
            'created_at' => CarbonImmutable::now()->subDay(),
            'is_admin'   => false,
        ]);

        $result = $this->repository->newUsersToday();

        $this->assertEquals(2, $result);
    }

    public function test_new_users_today_excludes_users_with_orders(): void
    {
        // Create user without orders
        $userWithoutOrders = User::factory()->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
            'is_admin'   => false,
        ]);

        // Create user with orders
        $userWithOrders = User::factory()->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(3),
            'is_admin'   => false,
        ]);

        Order::factory()->create(['user_id' => $userWithOrders->id]);

        $result = $this->repository->newUsersToday();

        $this->assertEquals(1, $result);
    }

    public function test_low_stock_items_uses_product_threshold(): void
    {
        // Product with custom threshold below limit
        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 5,
        ]);

        // Product with custom threshold above limit
        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 5,
        ]);

        $result = $this->repository->lowStockItems();

        $this->assertEquals(1, $result);
    }

    public function test_low_stock_items_uses_global_threshold_when_null(): void
    {
        Config::set('inventory.low_stock_threshold', 3);

        // Product without custom threshold, below global threshold
        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 0,
        ]);

        // Product without custom threshold, above global threshold
        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 5,
            'low_stock_threshold' => 0,
        ]);

        $result = $this->repository->lowStockItems();

        $this->assertEquals(1, $result);
    }

    public function test_low_stock_items_excludes_unmanaged_stock(): void
    {
        // Product with stock management disabled
        Product::factory()->create([
            'manage_stock'        => false,
            'stock_quantity'      => 1,
            'low_stock_threshold' => 5,
        ]);

        // Product with stock management enabled
        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 1,
            'low_stock_threshold' => 5,
        ]);

        $result = $this->repository->lowStockItems();

        $this->assertEquals(1, $result);
    }

    public function test_metrics_are_cached(): void
    {
        // Create test data
        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
        ]);

        // First call should hit the database
        $result1 = $this->repository->ordersToday();

        // Verify cache key exists
        $cacheKey = CacheKeys::dashboardMetric('orders_today', app()->getLocale());
        $this->assertTrue(Cache::has($cacheKey));

        // Second call should use cache
        $result2 = $this->repository->ordersToday();

        $this->assertEquals($result1, $result2);
    }

    public function test_cache_ttl_respects_configuration(): void
    {
        Config::set('dashboard.cache_ttl', 120);

        Order::factory()->create([
            'created_at' => CarbonImmutable::now()->startOfDay()->addHours(2),
        ]);

        $this->repository->ordersToday();

        $cacheKey = CacheKeys::dashboardMetric('orders_today', app()->getLocale());
        $this->assertTrue(Cache::has($cacheKey));

        // Cache should still exist after 60 seconds but before 120
        CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds(60));
        $this->assertTrue(Cache::has($cacheKey));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
}
