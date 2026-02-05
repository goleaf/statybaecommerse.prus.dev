<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    use RefreshDatabase;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);

        // Reset static caches between tests
        $this->resetHandlerCaches();
    }

    protected function tearDown(): void
    {
        $this->resetHandlerCaches();
        parent::tearDown();
    }

    private function resetHandlerCaches(): void
    {
        // Use the proper reset method for the refactored handler
        Handler::resetCache();
    }

    // === Boot Error Detection Tests ===

    public function test_detects_translatable_record_boot_error(): void
    {
        Log::spy();

        $exception = new Exception('Class App\Models\Product must implement translations() method from TranslatableRecord interface');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return $context['error_type'] === 'boot_failure'
                    && str_contains($context['actionable_message'], 'translations() method')
                    && ($context['interface_issue'] ?? false) === true
                    && isset($context['fix_suggestion'])
                    && isset($context['affected_models']);
            }));
    }

    public function test_detects_class_not_found_boot_error(): void
    {
        Log::spy();

        $exception = new Exception('Class App\Models\NonExistentModel not found');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return $context['error_type'] === 'boot_failure'
                    && str_contains($context['actionable_message'], 'composer dump-autoload');
            }));
    }

    public function test_detects_boot_error_in_model_file(): void
    {
        $exception = $this->createExceptionWithFile('Some error', '/app/Models/Product.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_detects_boot_error_in_contracts_directory(): void
    {
        $exception = $this->createExceptionWithFile('Interface error', '/app/Contracts/SomeContract.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_detects_boot_error_in_providers_directory(): void
    {
        $exception = $this->createExceptionWithFile('Provider error', '/app/Providers/AppServiceProvider.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_detects_boot_error_in_bootstrap_directory(): void
    {
        $exception = $this->createExceptionWithFile('Bootstrap error', '/bootstrap/app.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    // === Configuration Tests ===

    public function test_respects_boot_error_detection_config_disabled(): void
    {
        Config::set('exception-handling.boot_error_detection.enabled', false);

        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
    }

    public function test_uses_custom_error_patterns_from_config(): void
    {
        Config::set('exception-handling.boot_error_detection.patterns', ['custom_pattern']);

        $exception = new Exception('This contains custom_pattern error');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_uses_custom_paths_from_config(): void
    {
        Config::set('exception-handling.boot_error_detection.paths', ['/custom/path/']);

        $exception = $this->createExceptionWithFile('Custom path error', '/custom/path/SomeFile.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    // === Performance and Optimization Tests ===

    public function test_fast_exit_for_validation_exceptions(): void
    {
        $exception = new ValidationException(
            validator(['field' => 'value'], ['field' => 'required'])
        );

        $this->handler->report($exception);

        // Should not process validation exceptions for boot error detection
        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
    }

    public function test_fast_exit_for_authentication_exceptions(): void
    {
        $exception = new AuthenticationException('Unauthenticated');

        $this->handler->report($exception);

        // Should not process auth exceptions for boot error detection
        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
    }

    public function test_caches_configuration_values(): void
    {
        // First call should read config
        $exception = new Exception('TranslatableRecord interface error');
        $this->handler->report($exception);

        // Change config after first call
        Config::set('exception-handling.boot_error_detection.enabled', false);

        // Second call should use cached value (still enabled)
        $exception2 = new Exception('Another TranslatableRecord error');
        $this->handler->report($exception2);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->twice(); // Both calls should log because cache is used
    }

    // === Metrics and Monitoring Tests ===

    public function test_tracks_metrics_when_enabled(): void
    {
        Config::set('exception-handling.performance.track_boot_errors', true);

        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());

        Log::shouldHaveReceived('info')
            ->with('Boot error metric tracked', \Mockery::on(function ($context) {
                return isset($context['exception_type'])
                    && isset($context['error_pattern'])
                    && isset($context['file_type']);
            }));
    }

    public function test_does_not_track_metrics_when_disabled(): void
    {
        Config::set('exception-handling.performance.track_boot_errors', false);

        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());

        Log::shouldNotHaveReceived('info', ['Boot error metric tracked', \Mockery::any()]);
    }

    // === Actionable Messages Tests ===

    public function test_generates_correct_actionable_messages(): void
    {
        $testCases = [
            'translations()'           => 'Missing translations() method',
            'TranslatableRecord'       => 'TranslatableRecord interface implementation issue',
            'Class not found'          => 'Class autoloading issue',
            'Call to undefined method' => 'Method not found',
            'Parse error'              => 'Syntax error detected',
            'Syntax error'             => 'Syntax error detected',
            'Cannot declare class'     => 'Class declaration conflict',
            'Unknown error'            => 'Boot error detected',
        ];

        foreach ($testCases as $errorMessage => $expectedActionable) {
            Log::swap(app('log')); // Reset facade to the real logger before re-spying
            Log::spy();

            $exception = new Exception($errorMessage);
            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::on(function ($context) use ($expectedActionable) {
                    return str_contains($context['actionable_message'], $expectedActionable);
                }));
        }
    }

    // === Security Tests ===

    public function test_sanitizes_error_messages(): void
    {
        $sensitiveMessage = 'Error with password=secret123 and api_key=abc123';
        $exception = new Exception($sensitiveMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return ! str_contains($context['message'], 'secret123')
                    && ! str_contains($context['message'], 'abc123')
                    && str_contains($context['message'], '[REDACTED]');
            }));
    }

    public function test_sanitizes_file_paths(): void
    {
        $basePath = base_path();
        $fullPath = $basePath . '/app/Models/Product.php';
        $exception = $this->createExceptionWithFile('Error message', $fullPath);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) use ($basePath) {
                return ! str_contains($context['file'], $basePath)
                    && str_contains($context['file'], '[APP_ROOT]');
            }));
    }

    public function test_prevents_log_injection(): void
    {
        $maliciousMessage = "Error\nFAKE LOG ENTRY: Unauthorized access\rAnother fake entry";
        $exception = new Exception($maliciousMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return ! str_contains($context['message'], "\n")
                    && ! str_contains($context['message'], "\r")
                    && str_contains($context['message'], 'FAKE LOG ENTRY');
            }));
    }

    public function test_truncates_long_messages(): void
    {
        Config::set('exception-handling.security.max_message_length', 100);

        $longMessage = str_repeat('A', 200);
        $exception = new Exception($longMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return strlen($context['message']) <= 120 // 100 + truncation text
                    && str_contains($context['message'], '[truncated]');
            }));
    }

    // === Rate Limiting Tests ===

    public function test_rate_limits_boot_error_logging(): void
    {
        Log::spy();

        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        // First two errors should be logged
        for ($i = 0; $i < 2; $i++) {
            $exception = new Exception("Error {$i} with TranslatableRecord");
            $this->handler->report($exception);
        }

        // Third error should be rate limited
        $exception = new Exception('Rate limited error with TranslatableRecord');
        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->twice(); // Only first two should be logged
    }

    public function test_respects_rate_limit_disabled_config(): void
    {
        Log::spy();

        Config::set('exception-handling.security.rate_limit_enabled', false);

        // Should log all errors when rate limiting is disabled
        for ($i = 0; $i < 5; $i++) {
            $exception = new Exception("Error {$i} with TranslatableRecord");
            $this->handler->report($exception);
        }

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->times(5);
    }

    // === Context Building Tests ===

    public function test_builds_comprehensive_boot_error_context(): void
    {
        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

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

                return $context['error_type'] === 'boot_failure'
                    && $context['environment'] === 'testing';
            }));
    }

    public function test_includes_translatable_record_specific_context(): void
    {
        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return isset($context['fix_suggestion'])
                    && isset($context['affected_models'])
                    && ($context['interface_issue'] ?? false) === true
                    && in_array('Product', $context['affected_models']);
            }));
    }

    // === Logging Channel Tests ===

    public function test_uses_custom_log_channel_when_configured(): void
    {
        Config::set('exception-handling.boot_error_detection.log_channel', 'custom');

        // Mock the custom channel
        Log::shouldReceive('channel')
            ->with('custom')
            ->andReturnSelf();

        Log::shouldReceive('critical')
            ->with('Boot failure', \Mockery::any());

        $exception = new Exception('TranslatableRecord interface error');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_handles_invalid_log_channel_gracefully(): void
    {
        Config::set('exception-handling.boot_error_detection.log_channel', '../malicious');

        $exception = new Exception('TranslatableRecord interface error');

        // Should not throw exception even with invalid channel
        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    // === Error Handling Tests ===

    public function test_handles_logging_failures_gracefully(): void
    {
        // Mock Log to throw an exception on error() call
        Log::shouldReceive('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->andThrow(new Exception('Logging failed'));

        Log::shouldReceive('emergency')
            ->with('Boot error logging failed', \Mockery::on(function ($context) {
                return isset($context['original_error'])
                    && isset($context['file'])
                    && isset($context['line']);
            }));

        $exception = new Exception('TranslatableRecord interface error');

        // Should not throw an exception even if logging fails
        $this->handler->report($exception);
    }

    public function test_handles_complete_logging_failure_gracefully(): void
    {
        // Mock both error() and emergency() to fail
        Log::shouldReceive('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->andThrow(new Exception('Logging failed'));

        Log::shouldReceive('emergency')
            ->with('Boot error logging failed', \Mockery::any())
            ->andThrow(new Exception('Emergency logging failed'));

        $exception = new Exception('TranslatableRecord interface error');

        // Should not throw an exception even if all logging fails
        $this->expectNotToPerformAssertions();
        $this->handler->report($exception);
    }

    // === Legacy Exception Handling Tests ===

    public function test_ignores_validation_exceptions(): void
    {
        $exception = new ValidationException(
            validator(['field' => 'value'], ['field' => 'required'])
        );

        $this->handler->report($exception);

        // Should not log validation exceptions
        Log::shouldNotHaveReceived('error');
        Log::shouldNotHaveReceived('warning');
    }

    public function test_logs_type_errors_as_warnings(): void
    {
        $exception = new TypeError('Argument 1 passed to method() must be of type string');

        $this->handler->report($exception);

        Log::shouldHaveReceived('warning')
            ->with('Type error triggered by request parameters.', \Mockery::on(function ($context) {
                return isset($context['message']);
            }));
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
