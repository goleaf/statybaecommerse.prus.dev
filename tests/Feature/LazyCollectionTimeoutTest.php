<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\TimeoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

final class LazyCollectionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_takeUntilTimeout_stops_processing_after_timeout(): void
    {
        $startTime = now();
        $timeout = $startTime->addSeconds(2);
        $processedCount = 0;

        LazyCollection::times(INF)
            ->takeUntilTimeout($timeout)
            ->each(function (int $number) use (&$processedCount) {
                $processedCount++;
                usleep(100000); // 100ms delay to ensure timeout is reached
            });

        $duration = now()->diffInSeconds($startTime);
        
        // Should have processed some items but stopped due to timeout
        $this->assertGreaterThan(0, $processedCount);
        $this->assertLessThan(50, $processedCount); // Should not process too many due to timeout
        $this->assertGreaterThanOrEqual(2, $duration); // Should take at least 2 seconds
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
        $timeoutCollection = TimeoutService::forSearch($collection, 10);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(20, $processedCount);
    }

    public function test_timeout_service_for_recommendations(): void
    {
        $collection = LazyCollection::times(30);
        $timeoutCollection = TimeoutService::forRecommendations($collection, 30);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(30, $processedCount);
    }

    public function test_timeout_service_for_background_jobs(): void
    {
        $collection = LazyCollection::times(40);
        $timeoutCollection = TimeoutService::forBackgroundJob($collection, 5);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(40, $processedCount);
    }

    public function test_timeout_service_is_timeout_reached(): void
    {
        $pastTimeout = now()->subSeconds(10);
        $futureTimeout = now()->addSeconds(10);
        
        $this->assertTrue(TimeoutService::isTimeoutReached($pastTimeout));
        $this->assertFalse(TimeoutService::isTimeoutReached($futureTimeout));
    }

    public function test_timeout_service_get_remaining_time(): void
    {
        $futureTimeout = now()->addSeconds(30);
        $remainingTime = TimeoutService::getRemainingTime($futureTimeout);
        
        $this->assertGreaterThan(25, $remainingTime);
        $this->assertLessThanOrEqual(30, $remainingTime);
    }

    public function test_timeout_service_with_custom_timeout(): void
    {
        $collection = LazyCollection::times(25);
        $timeoutCollection = TimeoutService::withTimeout($collection, 15);
        
        $this->assertInstanceOf(LazyCollection::class, $timeoutCollection);
        
        $processedCount = 0;
        $timeoutCollection->each(function () use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(25, $processedCount);
    }

    public function test_lazy_collection_with_database_cursor_timeout(): void
    {
        // This test would require actual database records
        // For now, we'll test the concept with a mock collection
        
        $mockData = collect(range(1, 100))->map(fn($i) => (object)['id' => $i]);
        $collection = LazyCollection::make($mockData);
        
        $timeout = now()->addSeconds(1);
        $processedCount = 0;
        
        $collection
            ->takeUntilTimeout($timeout)
            ->each(function () use (&$processedCount) {
                $processedCount++;
                usleep(10000); // 10ms delay
            });
        
        // Should process some items but not all due to timeout
        $this->assertGreaterThan(0, $processedCount);
        $this->assertLessThan(100, $processedCount);
    }

    public function test_timeout_logging_functionality(): void
    {
        // Test that the logging method doesn't throw exceptions
        $this->expectNotToPerformAssertions();
        
        TimeoutService::logTimeoutInfo(
            'test_operation',
            50,
            now()->addSeconds(30),
            100
        );
    }
}
