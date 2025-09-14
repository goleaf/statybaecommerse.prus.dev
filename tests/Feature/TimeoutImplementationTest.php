<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\GenerateReportsCommand;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportGenerationService;
use App\Services\TimeoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class TimeoutImplementationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        User::factory()->count(10)->create();
        Product::factory()->count(5)->create();
    }

    public function test_timeout_service_with_timeout(): void
    {
        $collection = LazyCollection::make(range(1, 1000));
        $timeoutCollection = TimeoutService::withTimeout($collection, 5);
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
            usleep(1000); // Simulate work
        });
        
        $this->assertGreaterThan(0, $processedCount);
        $this->assertLessThanOrEqual(1000, $processedCount);
    }

    public function test_timeout_service_for_scheduled_tasks(): void
    {
        $collection = LazyCollection::make(range(1, 100));
        $timeoutCollection = TimeoutService::forScheduledTask($collection, 60);
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertGreaterThan(0, $processedCount);
    }

    public function test_timeout_service_remaining_time(): void
    {
        $timeout = now()->addSeconds(10);
        
        // Should have remaining time initially
        $remainingTime = TimeoutService::getRemainingTime($timeout);
        $this->assertGreaterThan(5, $remainingTime);
        
        // Wait a bit and check again
        sleep(2);
        $remainingTimeAfterWait = TimeoutService::getRemainingTime($timeout);
        $this->assertLessThan($remainingTime, $remainingTimeAfterWait);
    }

    public function test_lazy_collection_timeout_with_short_timeout(): void
    {
        $timeout = now()->addMilliseconds(50); // Very short timeout
        
        $collection = LazyCollection::make(range(1, 1000));
        $timeoutCollection = $collection->takeUntilTimeout($timeout);
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
            usleep(1000); // Simulate work
        });
        
        $this->assertGreaterThanOrEqual(0, $processedCount);
        $this->assertLessThan(1000, $processedCount);
    }

    public function test_report_generation_command(): void
    {
        $this->artisan(GenerateReportsCommand::class, [
            '--type' => 'system',
            '--format' => 'json',
            '--output' => 'test-reports'
        ])->assertExitCode(0);
    }

    public function test_timeout_service_for_import(): void
    {
        $collection = LazyCollection::make(range(1, 100));
        $timeoutCollection = TimeoutService::forImport($collection, 1); // 1 minute
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(100, $processedCount);
    }

    public function test_timeout_service_for_search(): void
    {
        $collection = LazyCollection::make(range(1, 50));
        $timeoutCollection = TimeoutService::forSearch($collection, 5); // 5 seconds
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(50, $processedCount);
    }

    public function test_timeout_service_for_recommendations(): void
    {
        $collection = LazyCollection::make(range(1, 20));
        $timeoutCollection = TimeoutService::forRecommendations($collection, 10); // 10 seconds
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(20, $processedCount);
    }

    public function test_timeout_service_for_background_job(): void
    {
        $collection = LazyCollection::make(range(1, 30));
        $timeoutCollection = TimeoutService::forBackgroundJob($collection, 2); // 2 minutes
        
        $processedCount = 0;
        $timeoutCollection->each(function ($item) use (&$processedCount) {
            $processedCount++;
        });
        
        $this->assertEquals(30, $processedCount);
    }

    public function test_timeout_service_is_timeout_reached(): void
    {
        $futureTimeout = now()->addSeconds(10);
        $pastTimeout = now()->subSeconds(10);
        
        $this->assertFalse(TimeoutService::isTimeoutReached($futureTimeout));
        $this->assertTrue(TimeoutService::isTimeoutReached($pastTimeout));
    }

    public function test_timeout_service_log_timeout_info(): void
    {
        Log::shouldReceive('info')->once();
        
        $timeout = now()->addSeconds(10);
        TimeoutService::logTimeoutInfo('test_operation', 100, $timeout, 200);
    }

    public function test_report_generation_service_sales_report(): void
    {
        $reportService = new ReportGenerationService();
        $report = $reportService->generateSalesReport();
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('daily_data', $report);
    }

    public function test_report_generation_service_product_analytics(): void
    {
        $reportService = new ReportGenerationService();
        $report = $reportService->generateProductAnalyticsReport();
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('products', $report);
    }

    public function test_report_generation_service_user_activity(): void
    {
        $reportService = new ReportGenerationService();
        $report = $reportService->generateUserActivityReport();
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('user_activity', $report);
    }

    public function test_report_generation_service_system_report(): void
    {
        $reportService = new ReportGenerationService();
        $report = $reportService->generateSystemReport();
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertArrayHasKey('timeout', $report);
        $this->assertArrayHasKey('sections', $report);
    }
}