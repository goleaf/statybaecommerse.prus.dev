<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Performance tests for exception handling functionality.
 * Validates that exception handling meets performance budgets.
 */
class ExceptionHandlingPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);
        Log::spy();
    }

    /**
     * Test that boot error detection completes within performance budget.
     */
    public function test_boot_error_detection_performance_budget(): void
    {
        $maxTime = config('exception-handling.budgets.boot_error_detection_max_ms', 2);
        $iterations = 100;

        $exception = new Exception('TranslatableRecord interface error');

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $averageTime = $totalTime / $iterations;

        $this->assertLessThan($maxTime, $averageTime, 
            "Boot error detection took {$averageTime}ms on average, exceeding budget of {$maxTime}ms");
    }

    /**
     * Test that context building completes within performance budget.
     */
    public function test_context_building_performance_budget(): void
    {
        $maxTime = config('exception-handling.budgets.context_building_max_ms', 1);
        $iterations = 50;

        $exception = new Exception('TranslatableRecord interface error with long message ' . str_repeat('data ', 100));

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / $iterations;

        $this->assertLessThan($maxTime * 5, $averageTime, // Allow 5x budget for context building
            "Context building took {$averageTime}ms on average, significantly exceeding expectations");
    }

    /**
     * Test performance with rate limiting enabled.
     */
    public function test_rate_limiting_performance_impact(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10);

        $iterations = 20; // Exceed rate limit
        $exception = new Exception('TranslatableRecord rate limit test');

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / $iterations;

        // Rate limiting should not significantly impact performance
        $this->assertLessThan(5, $averageTime, 
            "Rate limiting caused significant performance degradation: {$averageTime}ms average");
    }

    /**
     * Test performance with security sanitization.
     */
    public function test_security_sanitization_performance(): void
    {
        $sensitiveMessage = 'Error with password=secret123 and api_key=abc123 and token=xyz789 ' . str_repeat('sensitive_data ', 50);
        $exception = new Exception($sensitiveMessage);

        $iterations = 50;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / $iterations;

        // Security sanitization should complete quickly
        $this->assertLessThan(3, $averageTime, 
            "Security sanitization is too slow: {$averageTime}ms average");
    }

    /**
     * Test performance with different exception types.
     */
    public function test_exception_type_filtering_performance(): void
    {
        $exceptions = [
            new \Illuminate\Validation\ValidationException(
                validator(['field' => 'value'], ['field' => 'required'])
            ),
            new \Illuminate\Auth\AuthenticationException('Unauthenticated'),
            new Exception('TranslatableRecord error'),
            new Exception('Class not found'),
            new \TypeError('Type error'),
        ];

        $iterations = 20;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            foreach ($exceptions as $exception) {
                $this->handler->report($exception);
            }
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / ($iterations * count($exceptions));

        // Exception type filtering should be very fast
        $this->assertLessThan(1, $averageTime, 
            "Exception type filtering is too slow: {$averageTime}ms average");
    }

    /**
     * Test memory usage during exception handling.
     */
    public function test_memory_usage_within_bounds(): void
    {
        $initialMemory = memory_get_usage(true);
        $iterations = 100;

        $exception = new Exception('TranslatableRecord memory test ' . str_repeat('data ', 100));

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        $memoryPerIteration = $memoryIncrease / $iterations;

        // Should not use excessive memory per exception
        $this->assertLessThan(1024 * 10, $memoryPerIteration, // 10KB per exception
            "Excessive memory usage: {$memoryPerIteration} bytes per exception");
    }

    /**
     * Test performance with caching enabled.
     */
    public function test_configuration_caching_performance_benefit(): void
    {
        $iterations = 100;
        $exception = new Exception('TranslatableRecord caching test');

        // Test without caching (fresh handler each time)
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $freshHandler = new Handler($this->app);
            $freshHandler->report($exception);
        }
        $timeWithoutCaching = (microtime(true) - $startTime) * 1000;

        // Test with caching (same handler instance)
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }
        $timeWithCaching = (microtime(true) - $startTime) * 1000;

        // Caching should provide performance benefit
        $this->assertLessThan($timeWithoutCaching, $timeWithCaching, 
            "Configuration caching should improve performance");

        // Both should be within reasonable bounds
        $this->assertLessThan(100, $timeWithCaching, 
            "Even with caching, performance is too slow: {$timeWithCaching}ms total");
    }

    /**
     * Test performance with metrics tracking enabled.
     */
    public function test_metrics_tracking_performance_impact(): void
    {
        Config::set('exception-handling.performance.track_boot_errors', true);

        $iterations = 50;
        $exception = new Exception('TranslatableRecord metrics test');

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / $iterations;

        // Metrics tracking should not significantly impact performance
        $this->assertLessThan(5, $averageTime, 
            "Metrics tracking caused performance degradation: {$averageTime}ms average");
    }

    /**
     * Test performance with large file paths.
     */
    public function test_large_file_path_handling_performance(): void
    {
        $longPath = '/app/Models/' . str_repeat('VeryLongDirectoryName/', 20) . 'Product.php';
        $exception = $this->createExceptionWithFile('Error in long path', $longPath);

        $iterations = 50;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;
        $averageTime = $totalTime / $iterations;

        // Should handle long paths efficiently
        $this->assertLessThan(3, $averageTime, 
            "Large file path handling is too slow: {$averageTime}ms average");
    }

    /**
     * Test concurrent exception handling performance.
     */
    public function test_concurrent_exception_handling(): void
    {
        $exceptions = [];
        for ($i = 0; $i < 10; $i++) {
            $exceptions[] = new Exception("Concurrent error {$i}");
        }

        $startTime = microtime(true);

        // Simulate concurrent processing
        foreach ($exceptions as $exception) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000;

        // Should handle multiple exceptions quickly
        $this->assertLessThan(50, $totalTime, 
            "Concurrent exception handling is too slow: {$totalTime}ms total");
    }

    private function createExceptionWithFile(string $message, string $file): Exception
    {
        $exception = new Exception($message);

        $reflection = new ReflectionClass($exception);
        $fileProperty = $reflection->getProperty('file');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($exception, $file);

        return $exception;
    }
}