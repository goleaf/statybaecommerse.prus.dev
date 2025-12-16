<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use App\Support\Exceptions\BootErrorDetector;
use App\Support\Exceptions\BootErrorRateLimiter;
use App\Support\Exceptions\ErrorMessageSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Tests for the refactored exception handler.
 */
class RefactoredHandlerTest extends TestCase
{
    use RefreshDatabase;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);
        Log::spy();

        // Reset all caches between tests
        Handler::resetCache();
    }

    public function test_detects_translatable_record_error_with_refactored_code(): void
    {
        $exception = new Exception('Class App\Models\Product must implement translations() method from TranslatableRecord interface');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return $context['error_type'] === 'boot_failure'
                    && str_contains($context['actionable_message'], 'translations() method')
                    && ($context['interface_issue'] ?? false) === true;
            }));
    }

    public function test_boot_error_detector_works_independently(): void
    {
        $detector = new BootErrorDetector;

        $translatableError = new Exception('TranslatableRecord interface error');
        $validationError = new \Illuminate\Validation\ValidationException(
            validator(['field' => 'value'], ['field' => 'required'])
        );

        $this->assertTrue($detector->shouldProcess($translatableError));
        $this->assertFalse($detector->shouldProcess($validationError));
        $this->assertTrue($detector->isTranslatableRecordError($translatableError));
    }

    public function test_error_message_sanitizer_works_independently(): void
    {
        $sanitizer = new ErrorMessageSanitizer;

        $sensitiveMessage = 'Error with password=secret123 and api_key=abc123';
        $sanitized = $sanitizer->sanitizeMessage($sensitiveMessage);

        $this->assertStringNotContainsString('secret123', $sanitized);
        $this->assertStringNotContainsString('abc123', $sanitized);
        $this->assertStringContainsString('[REDACTED]', $sanitized);
    }

    public function test_rate_limiter_works_independently(): void
    {
        $rateLimiter = new BootErrorRateLimiter;

        // Reset to ensure clean state
        BootErrorRateLimiter::reset();

        // First few calls should not be rate limited
        $this->assertFalse($rateLimiter->isRateLimited());
        $this->assertFalse($rateLimiter->isRateLimited());
    }

    public function test_refactored_handler_maintains_performance(): void
    {
        $exception = new Exception('TranslatableRecord performance test');

        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            $this->handler->report($exception);
        }

        $totalTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Should complete quickly even with the new architecture
        $this->assertLessThan(50, $totalTime, "Refactored handler is too slow: {$totalTime}ms");
    }

    public function test_cache_reset_functionality(): void
    {
        // Trigger some caching
        $exception = new Exception('TranslatableRecord cache test');
        $this->handler->report($exception);

        // Reset should not throw errors
        Handler::resetCache();

        // Should still work after reset
        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->twice();
    }

    public function test_components_are_properly_injected(): void
    {
        // Test that the handler properly creates and uses the new components
        $exception = new Exception('Component injection test with TranslatableRecord');

        $this->handler->report($exception);

        // Should log with proper context structure
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $requiredKeys = [
                    'error_type', 'exception_class', 'message', 'file', 'line',
                    'actionable_message', 'timestamp', 'environment', 'request_id',
                ];

                foreach ($requiredKeys as $key) {
                    if (! isset($context[$key])) {
                        return false;
                    }
                }

                return true;
            }));
    }
}
