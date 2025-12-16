<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Simplified tests for exception handler boot error detection.
 * Uses individual test isolation to avoid Mockery conflicts.
 */
class HandlerSimpleTest extends TestCase
{
    use RefreshDatabase;

    // === Basic Boot Error Detection ===

    public function test_detects_translatable_record_error(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception('TranslatableRecord interface not implemented');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->once();
    }

    public function test_detects_class_not_found_error(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception('Class App\Models\Product not found');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->once();
    }

    public function test_ignores_validation_exceptions(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new ValidationException(
            validator(['field' => 'value'], ['field' => 'required'])
        );

        $handler->report($exception);

        Log::shouldNotHaveReceived('error');
    }

    public function test_ignores_authentication_exceptions(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new AuthenticationException('Unauthenticated');

        $handler->report($exception);

        Log::shouldNotHaveReceived('error');
    }

    public function test_logs_type_errors_as_warnings(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new \TypeError('Type error message');

        $handler->report($exception);

        Log::shouldHaveReceived('warning')
            ->with('Type error triggered by request parameters.', \Mockery::any())
            ->once();
    }

    // === Configuration Tests ===

    public function test_respects_disabled_configuration(): void
    {
        Log::spy();
        Config::set('exception-handling.boot_error_detection.enabled', false);
        
        $handler = app(Handler::class);
        $exception = new Exception('TranslatableRecord error');

        $handler->report($exception);

        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
    }

    public function test_uses_custom_patterns(): void
    {
        Log::spy();
        Config::set('exception-handling.boot_error_detection.patterns', ['custom_error']);
        
        $handler = app(Handler::class);
        $exception = new Exception('This is a custom_error message');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->once();
    }

    // === Security Tests ===

    public function test_sanitizes_sensitive_data(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception('Error with password=secret123');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return !str_contains($context['message'], 'secret123')
                    && str_contains($context['message'], '[REDACTED]');
            }))
            ->once();
    }

    public function test_prevents_log_injection(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception("Error\nFAKE LOG ENTRY");

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return !str_contains($context['message'], "\n");
            }))
            ->once();
    }

    // === Rate Limiting Tests ===

    public function test_rate_limits_errors(): void
    {
        Log::spy();
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1);
        
        $handler = app(Handler::class);

        // First error should be logged
        $exception1 = new Exception('First error');
        $handler->report($exception1);

        // Second error should be rate limited
        $exception2 = new Exception('Second error');
        $handler->report($exception2);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->once(); // Only first should be logged
    }

    // === Context Building Tests ===

    public function test_builds_complete_context(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception('TranslatableRecord error');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $requiredKeys = [
                    'error_type', 'exception_class', 'message', 'file', 'line',
                    'actionable_message', 'timestamp', 'environment'
                ];

                foreach ($requiredKeys as $key) {
                    if (!isset($context[$key])) {
                        return false;
                    }
                }

                return $context['error_type'] === 'boot_failure';
            }))
            ->once();
    }

    public function test_includes_translatable_record_context(): void
    {
        Log::spy();
        
        $handler = app(Handler::class);
        $exception = new Exception('translations() method missing');

        $handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return isset($context['fix_suggestion'])
                    && isset($context['affected_models'])
                    && ($context['interface_issue'] ?? false) === true;
            }))
            ->once();
    }

    // === Error Handling Tests ===

    public function test_handles_logging_failures(): void
    {
        // Mock Log to fail on error() but succeed on emergency()
        Log::shouldReceive('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->andThrow(new Exception('Logging failed'));

        Log::shouldReceive('emergency')
            ->with('Boot error logging failed', \Mockery::any())
            ->once();

        $handler = app(Handler::class);
        $exception = new Exception('TranslatableRecord error');

        // Should not throw exception even if logging fails
        $handler->report($exception);

        $this->assertTrue(true); // Test passes if no exception thrown
    }

    // === Performance Tests ===

    public function test_performance_within_budget(): void
    {
        $handler = app(Handler::class);
        $exception = new Exception('TranslatableRecord performance test');

        $startTime = microtime(true);
        $handler->report($exception);
        $executionTime = (microtime(true) - $startTime) * 1000;

        // Should complete within 10ms (generous budget for test environment)
        $this->assertLessThan(10, $executionTime, 
            "Exception handling took {$executionTime}ms, exceeding budget");
    }

    // === Helper Methods ===

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