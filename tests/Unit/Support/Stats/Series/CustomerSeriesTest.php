<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Stats\Series;

use App\Models\Customer;
use App\Models\Order;
use App\Support\Cache\CacheKeys;
use App\Support\Stats\Series\CustomerSeries;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CustomerSeriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the customer order helper returns the expected totals and caches the payload.
     */
    public function test_daily_orders_returns_expected_values_and_caches_result(): void
    {
        Cache::flush();

        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $customer = Customer::factory()->create();

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
            'total'      => 120.50,
        ]);

        Order::withoutTimestamps(function () use ($firstOrder, $customer): void {
            $firstOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
            'total'      => 80.00,
        ]);

        Order::withoutTimestamps(function () use ($secondOrder, $customer): void {
            $secondOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $ignoredOrder = Order::factory()->completed()->create([
            'created_at' => CarbonImmutable::now()->subDays(5),
            'updated_at' => CarbonImmutable::now()->subDays(5),
            'total'      => 45.00,
        ]);

        Order::withoutTimestamps(function () use ($ignoredOrder, $customer): void {
            $ignoredOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $series = CustomerSeries::dailyOrders($customer, 3);

        $expectedLabels = [
            $firstDay->isoFormat('MMM D'),
            $secondDay->isoFormat('MMM D'),
            CarbonImmutable::now()->isoFormat('MMM D'),
        ];

        $expectedOrders = [1, 1, 0];
        $expectedRevenue = [120.5, 80.0, 0.0];

        self::assertSame($expectedLabels, $series['labels']);
        self::assertSame($expectedOrders, $series['orders']);
        self::assertSame($expectedRevenue, $series['revenue']);

        $cacheKey = CacheKeys::customerOrdersSeries($customer->getKey(), 3);
        self::assertTrue(Cache::has($cacheKey));

        CarbonImmutable::setTestNow();
    }
}
