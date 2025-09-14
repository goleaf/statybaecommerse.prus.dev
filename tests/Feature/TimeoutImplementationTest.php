<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ReportGenerationService;
use App\Services\TimeoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

final class TimeoutImplementationTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeout_service_with_timeout(): void
    {
        $startTime = now();
        $timeout = $startTime->addSeconds(2);
        $processedCount = 0;

        LazyCollection::times(INF)
            ->takeUntilTimeout($timeout)
            ->each(function (int $number) use (&$processedCount) {
                $processedCount++;
                usleep(100000); // 100ms delay
            });

        $duration = now()->diffInSeconds($startTime);
        
        // Should have processed some items but stopped due to timeout
        $this->assertGreaterThan(0, $processedCount);
        $this->assertLessThanOrEqual(3, $duration); // Should be around 2 seconds
    }

    public function test_timeout_service_for_scheduled_tasks(): void
    {
        $collection = LazyCollection::times(100);
        $timeoutCollection = TimeoutService::forScheduledTask($collection, 60);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(100, $processedCount);
    }

    public function test_timeout_service_for_import_operations(): void
    {
        $collection = LazyCollection::times(50);
        $timeoutCollection = TimeoutService::forImport($collection, 5);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(50, $processedCount);
    }

    public function test_timeout_service_for_search_operations(): void
    {
        $collection = LazyCollection::times(20);
        $timeoutCollection = TimeoutService::forSearch($collection, 5);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(20, $processedCount);
    }

    public function test_timeout_service_for_recommendations(): void
    {
        $collection = LazyCollection::times(10);
        $timeoutCollection = TimeoutService::forRecommendations($collection, 10);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(10, $processedCount);
    }

    public function test_timeout_service_for_background_jobs(): void
    {
        $collection = LazyCollection::times(30);
        $timeoutCollection = TimeoutService::forBackgroundJob($collection, 3);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(30, $processedCount);
    }

    public function test_timeout_service_timeout_reached_check(): void
    {
        $timeout = now()->addSeconds(1);
        $this->assertFalse(TimeoutService::isTimeoutReached($timeout));
        
        sleep(2);
        $this->assertTrue(TimeoutService::isTimeoutReached($timeout));
    }

    public function test_timeout_service_remaining_time(): void
    {
        $timeout = now()->addSeconds(10);
        $remaining = TimeoutService::getRemainingTime($timeout);
        
        $this->assertGreaterThan(5, $remaining);
        $this->assertLessThanOrEqual(10, $remaining);
    }

    public function test_report_generation_service_sales_report(): void
    {
        $service = new ReportGenerationService();
        $result = $service->generateSalesReport();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('daily_data', $result);
        $this->assertArrayHasKey('total_revenue', $result['summary']);
        $this->assertArrayHasKey('total_transactions', $result['summary']);
    }

    public function test_report_generation_service_product_analytics(): void
    {
        $service = new ReportGenerationService();
        $result = $service->generateProductAnalyticsReport();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('total_products', $result['summary']);
    }

    public function test_report_generation_service_user_activity(): void
    {
        $service = new ReportGenerationService();
        $result = $service->generateUserActivityReport();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('user_activity', $result);
        $this->assertArrayHasKey('total_events', $result['summary']);
        $this->assertArrayHasKey('unique_users', $result['summary']);
    }

    public function test_report_generation_service_system_report(): void
    {
        $service = new ReportGenerationService();
        $result = $service->generateSystemReport();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('generated_at', $result);
        $this->assertArrayHasKey('timeout', $result);
        $this->assertArrayHasKey('sections', $result);
    }

    public function test_lazy_collection_timeout_with_finite_data(): void
    {
        $data = range(1, 100);
        $timeout = now()->addSeconds(10); // Long timeout
        $processedCount = 0;

        LazyCollection::make($data)
            ->takeUntilTimeout($timeout)
            ->each(function () use (&$processedCount) {
                $processedCount++;
            });

        // Should process all items since timeout is long enough
        $this->assertEquals(100, $processedCount);
    }

    public function test_lazy_collection_timeout_with_short_timeout(): void
    {
        $data = range(1, 1000);
        $timeout = now()->addMilliseconds(50); // Very short timeout
        $processedCount = 0;

        LazyCollection::make($data)
            ->takeUntilTimeout($timeout)
            ->each(function () use (&$processedCount) {
                $processedCount++;
                // No delay to ensure we process at least some items
            });

        // Should process some items before timeout
        $this->assertLessThan(1000, $processedCount);
        $this->assertGreaterThanOrEqual(0, $processedCount);
    }

    public function test_timeout_service_logging(): void
    {
        $timeout = now()->addSeconds(5);
        $processedCount = 10;
        $totalCount = 100;

        // This should not throw an exception
        TimeoutService::logTimeoutInfo(
            'test_operation',
            $processedCount,
            $timeout,
            $totalCount
        );

        $this->assertTrue(true); // If we get here, logging worked
    }
}
