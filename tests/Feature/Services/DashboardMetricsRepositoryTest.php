<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Dashboard\DashboardMetricsRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\TestCase;

final class DashboardMetricsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 1, 10, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_metrics_match_database_values(): void
    {
        Order::factory()->completed()->create(['total' => 120, 'created_at' => CarbonImmutable::now()->subDay()]);
        Order::factory()->completed()->create(['total' => 80, 'created_at' => CarbonImmutable::now()]);
        Order::factory()->completed()->create(['total' => 45, 'created_at' => CarbonImmutable::now()->subDays(10)]);

        User::factory()->create(['created_at' => CarbonImmutable::now()]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDay()]);

        Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 5,
        ]);

        $repository = app(DashboardMetricsRepository::class);

        self::assertSame(1, $repository->ordersToday());
        self::assertSame(200.0, $repository->revenueLastSevenDays());
        self::assertSame(1, $repository->newUsersToday());
        self::assertSame(1, $repository->lowStockItems());
    }
}
