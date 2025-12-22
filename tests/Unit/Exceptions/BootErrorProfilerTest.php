<?php

declare(strict_types=1);

use App\Support\Exceptions\BootErrorProfiler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Test suite for BootErrorProfiler functionality.
 */
class BootErrorProfilerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_profiling_disabled_by_default(): void
    {
        Config::set('exception-handling.performance.enable_profiling', false);

        BootErrorProfiler::startTiming('test_operation');
        BootErrorProfiler::endTiming('test_operation');
        BootErrorProfiler::recordMemoryUsage('test_operation');
        BootErrorProfiler::incrementCallCount('test_operation');

        $data = BootErrorProfiler::getProfilingData();

        $this->assertEmpty($data);
    }

    public function test_timing_measurement_works(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        BootErrorProfiler::startTiming('test_operation');
        usleep(1000); // 1ms delay
        $duration = BootErrorProfiler::endTiming('test_operation');

        $this->assertGreaterThan(0, $duration);
        $this->assertLessThan(0.1, $duration); // Should be less than 100ms

        $data = BootErrorProfiler::getProfilingData();
        $this->assertArrayHasKey('timings', $data);
        $this->assertArrayHasKey('test_operation', $data['timings']);
        $this->assertCount(1, $data['timings']['test_operation']);
    }

    public function test_memory_usage_recording(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        BootErrorProfiler::recordMemoryUsage('test_operation');

        $data = BootErrorProfiler::getProfilingData();
        $this->assertArrayHasKey('memory_usage', $data);
        $this->assertArrayHasKey('test_operation', $data['memory_usage']);
        $this->assertCount(1, $data['memory_usage']['test_operation']);
        $this->assertGreaterThan(0, $data['memory_usage']['test_operation'][0]);
    }

    public function test_call_count_tracking(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        BootErrorProfiler::incrementCallCount('test_operation');
        BootErrorProfiler::incrementCallCount('test_operation');
        BootErrorProfiler::incrementCallCount('test_operation');

        $data = BootErrorProfiler::getProfilingData();
        $this->assertArrayHasKey('call_counts', $data);
        $this->assertArrayHasKey('test_operation', $data['call_counts']);
        $this->assertSame(3, $data['call_counts']['test_operation']);
    }

    public function test_performance_regression_detection(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);
        Config::set('exception-handling.budgets.test_operation_max_ms', 1); // 1ms budget

        // Record a slow operation
        BootErrorProfiler::startTiming('test_operation');
        usleep(2000); // 2ms delay - exceeds budget
        BootErrorProfiler::endTiming('test_operation');

        $regressions = BootErrorProfiler::detectPerformanceRegression();

        $this->assertNotEmpty($regressions);
        $this->assertSame('timing', $regressions[0]['type']);
        $this->assertSame('test_operation', $regressions[0]['operation']);
        $this->assertGreaterThan(1, $regressions[0]['average_ms']);
    }

    public function test_memory_regression_detection(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);
        Config::set('exception-handling.budgets.test_operation_max_memory_mb', 0.001); // Very small budget

        BootErrorProfiler::recordMemoryUsage('test_operation');

        $regressions = BootErrorProfiler::detectPerformanceRegression();

        $this->assertNotEmpty($regressions);
        $this->assertSame('memory', $regressions[0]['type']);
        $this->assertSame('test_operation', $regressions[0]['operation']);
    }

    public function test_data_retention_limits(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        // Record more than 100 measurements
        for ($i = 0; $i < 150; $i++) {
            BootErrorProfiler::startTiming('test_operation');
            BootErrorProfiler::endTiming('test_operation');
        }

        $data = BootErrorProfiler::getProfilingData();
        
        // Should keep only last 100 measurements
        $this->assertLessThanOrEqual(100, count($data['timings']['test_operation']));
    }

    public function test_multiple_operations_tracking(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        BootErrorProfiler::startTiming('operation_a');
        BootErrorProfiler::endTiming('operation_a');

        BootErrorProfiler::startTiming('operation_b');
        BootErrorProfiler::endTiming('operation_b');

        BootErrorProfiler::incrementCallCount('operation_a');
        BootErrorProfiler::incrementCallCount('operation_b');
        BootErrorProfiler::incrementCallCount('operation_b');

        $data = BootErrorProfiler::getProfilingData();

        $this->assertArrayHasKey('operation_a', $data['timings']);
        $this->assertArrayHasKey('operation_b', $data['timings']);
        $this->assertSame(1, $data['call_counts']['operation_a']);
        $this->assertSame(2, $data['call_counts']['operation_b']);
    }

    public function test_timing_without_start_returns_zero(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        $duration = BootErrorProfiler::endTiming('nonexistent_operation');

        $this->assertSame(0.0, $duration);
    }

    public function test_profiling_data_includes_timestamp(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);

        $data = BootErrorProfiler::getProfilingData();

        $this->assertArrayHasKey('timestamp', $data);
        $this->assertIsString($data['timestamp']);
    }

    public function test_severity_classification_in_regressions(): void
    {
        Config::set('exception-handling.performance.enable_profiling', true);
        
        // Simulate timing data directly instead of relying on usleep which can be inconsistent
        $currentHour = date('Y-m-d-H');
        
        // High severity: 5ms average with 2ms budget (>2x = high)
        Config::set('exception-handling.budgets.slow_operation_max_ms', 2);
        cache()->put('boot_error_profiling_timings_' . $currentHour, [
            'slow_operation' => [0.005] // 5ms
        ], now()->addMinutes(30));
        
        // Medium severity: 3ms average with 2ms budget (1.5x = medium)
        Config::set('exception-handling.budgets.medium_operation_max_ms', 2);
        cache()->put('boot_error_profiling_timings_' . $currentHour, [
            'slow_operation' => [0.005], // 5ms
            'medium_operation' => [0.003] // 3ms
        ], now()->addMinutes(30));

        $regressions = BootErrorProfiler::detectPerformanceRegression();

        $this->assertCount(2, $regressions);
        
        $highSeverity = collect($regressions)->firstWhere('operation', 'slow_operation');
        $mediumSeverity = collect($regressions)->firstWhere('operation', 'medium_operation');

        $this->assertNotNull($highSeverity);
        $this->assertNotNull($mediumSeverity);
        $this->assertSame('high', $highSeverity['severity']);
        $this->assertSame('medium', $mediumSeverity['severity']);
    }
}