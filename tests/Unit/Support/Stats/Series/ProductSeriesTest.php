<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Stats\Series;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Stats\Series\ProductSeries;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class ProductSeriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the product sales helper returns an ordered dataset and stores it in cache.
     */
    public function test_daily_sales_returns_expected_values_and_caches_result(): void
    {
        Cache::flush();

        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $product = Product::factory()->create([
            'price' => 20,
        ]);

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
        ]);

        OrderItem::factory()
            ->forOrder($firstOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 2,
                'created_at' => $firstDay,
                'updated_at' => $firstDay,
            ]);

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
        ]);

        OrderItem::factory()
            ->forOrder($secondOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 3,
                'created_at' => $secondDay,
                'updated_at' => $secondDay,
            ]);

        $ignoredOrder = Order::factory()->completed()->create([
            'created_at' => CarbonImmutable::now()->subDays(5),
            'updated_at' => CarbonImmutable::now()->subDays(5),
        ]);

        OrderItem::factory()
            ->forOrder($ignoredOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 5,
                'created_at' => CarbonImmutable::now()->subDays(5),
                'updated_at' => CarbonImmutable::now()->subDays(5),
            ]);

        $series = ProductSeries::dailySales($product, 3);

        $expectedLabels = [
            $firstDay->isoFormat('MMM D'),
            $secondDay->isoFormat('MMM D'),
            CarbonImmutable::now()->isoFormat('MMM D'),
        ];

        $expectedQuantities = [2, 3, 0];
        $expectedRevenue = [40.0, 60.0, 0.0];

        self::assertSame($expectedLabels, $series['labels']);
        self::assertSame($expectedQuantities, $series['quantities']);
        self::assertSame($expectedRevenue, $series['revenue']);

        $cacheKey = CacheKeys::productSalesSeries($product->getKey(), 3);
        self::assertTrue(Cache::has($cacheKey));

        CarbonImmutable::setTestNow();
    }
}
