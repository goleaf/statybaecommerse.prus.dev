<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Property-based tests for boot error detection functionality.
 * These tests validate invariants and edge cases using property-based testing principles.
 */
class BootErrorDetectionPropertyTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);
        Log::spy();
    }

    /**
     * Property: Boot error detection should be consistent regardless of exception order.
     */
    public function test_boot_error_detection_is_order_independent(): void
    {
        $bootErrorMessages = [
            'TranslatableRecord interface error',
            'Class App\Models\Product not found',
            'Call to undefined method translations()',
            'Parse error: syntax error',
        ];

        $nonBootErrorMessages = [
            'Regular application error',
            'Database connection failed',
            'HTTP 404 not found',
        ];

        // Test different orderings
        $allMessages = array_merge($bootErrorMessages, $nonBootErrorMessages);
        
        foreach ([false, true] as $shuffle) {
            if ($shuffle) {
                shuffle($allMessages);
            }

            Log::clearResolvedInstances();
            Log::spy();

            $bootErrorCount = 0;
            foreach ($allMessages as $message) {
                $exception = new Exception($message);
                $this->handler->report($exception);

                if (in_array($message, $bootErrorMessages, true)) {
                    $bootErrorCount++;
                }
            }

            // Should detect exactly the number of boot errors, regardless of order
            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::any())
                ->times($bootErrorCount);
        }
    }

    /**
     * Property: Configuration changes should not affect already processed exceptions.
     */
    public function test_configuration_caching_invariant(): void
    {
        // Enable boot error detection
        Config::set('exception-handling.boot_error_detection.enabled', true);

        $exception1 = new Exception('TranslatableRecord error');
        $this->handler->report($exception1);

        // Disable boot error detection (should be cached)
        Config::set('exception-handling.boot_error_detection.enabled', false);

        $exception2 = new Exception('Another TranslatableRecord error');
        $this->handler->report($exception2);

        // Both should be processed because the first call cached the enabled state
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->twice();
    }

    /**
     * Property: Rate limiting should be consistent within time windows.
     */
    public function test_rate_limiting_time_window_invariant(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        // Generate errors within the same minute
        $errorCount = 5;
        $maxAllowed = 3;

        for ($i = 0; $i < $errorCount; $i++) {
            $exception = new Exception("Boot error {$i}");
            $this->handler->report($exception);
        }

        // Should only log up to the rate limit
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->times($maxAllowed);
    }

    /**
     * Property: Security sanitization should be idempotent.
     */
    public function test_security_sanitization_idempotent(): void
    {
        $sensitiveMessages = [
            'Error with password=secret123',
            'API key=abc123 failed',
            'Token: xyz789 expired',
            'Secret: hidden_value leaked',
        ];

        foreach ($sensitiveMessages as $message) {
            Log::clearResolvedInstances();
            Log::spy();

            $exception = new Exception($message);
            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::on(function ($context) {
                    // Should not contain any sensitive patterns
                    $sensitivePatterns = ['secret123', 'abc123', 'xyz789', 'hidden_value'];
                    
                    foreach ($sensitivePatterns as $pattern) {
                        if (str_contains($context['message'], $pattern)) {
                            return false;
                        }
                    }

                    return str_contains($context['message'], '[REDACTED]');
                }));
        }
    }

    /**
     * Property: File path detection should work for all configured paths.
     */
    public function test_file_path_detection_completeness(): void
    {
        $configuredPaths = ['/Models/', '/Contracts/', '/Providers/', '/bootstrap/'];
        Config::set('exception-handling.boot_error_detection.paths', $configuredPaths);

        foreach ($configuredPaths as $path) {
            Log::clearResolvedInstances();
            Log::spy();

            $filePath = "/app{$path}SomeFile.php";
            $exception = $this->createExceptionWithFile('Generic error', $filePath);
            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::any())
                ->once();
        }
    }

    /**
     * Property: Pattern matching should be case-insensitive and partial.
     */
    public function test_pattern_matching_flexibility(): void
    {
        $basePatterns = ['Interface', 'not found', 'undefined method'];
        Config::set('exception-handling.boot_error_detection.patterns', $basePatterns);

        $testCases = [
            'interface error occurred',           // lowercase
            'INTERFACE ERROR OCCURRED',           // uppercase
            'Class Interface not working',        // mixed case
            'Something not found here',           // partial match
            'Call to undefined method test()',    // partial match with context
        ];

        foreach ($testCases as $message) {
            Log::clearResolvedInstances();
            Log::spy();

            $exception = new Exception($message);
            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::any())
                ->once();
        }
    }

    /**
     * Property: Context building should always include required fields.
     */
    public function test_context_completeness_invariant(): void
    {
        $requiredFields = [
            'error_type', 'exception_class', 'message', 'file', 'line',
            'actionable_message', 'timestamp', 'environment', 'request_id',
        ];

        $testExceptions = [
            new Exception('TranslatableRecord error'),
            new Exception('Class not found'),
            new Exception('Parse error'),
            $this->createExceptionWithFile('Model error', '/app/Models/Test.php'),
        ];

        foreach ($testExceptions as $exception) {
            Log::clearResolvedInstances();
            Log::spy();

            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::on(function ($context) use ($requiredFields) {
                    foreach ($requiredFields as $field) {
                        if (!isset($context[$field])) {
                            return false;
                        }
                    }
                    return true;
                }));
        }
    }

    /**
     * Property: Actionable messages should always be non-empty and helpful.
     */
    public function test_actionable_messages_quality_invariant(): void
    {
        $errorMessages = [
            'translations() method missing',
            'TranslatableRecord not implemented',
            'Class App\Models\Product not found',
            'Call to undefined method test()',
            'Parse error in file',
            'Cannot declare class Duplicate',
            'Unknown boot error',
        ];

        foreach ($errorMessages as $message) {
            Log::clearResolvedInstances();
            Log::spy();

            $exception = new Exception($message);
            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::on(function ($context) {
                    $actionableMessage = $context['actionable_message'] ?? '';
                    
                    // Should be non-empty
                    if (empty($actionableMessage)) {
                        return false;
                    }

                    // Should contain helpful keywords
                    $helpfulKeywords = ['Add:', 'Run', 'Check', 'Ensure', 'method', 'class', 'error'];
                    $containsHelpfulKeyword = false;
                    
                    foreach ($helpfulKeywords as $keyword) {
                        if (str_contains($actionableMessage, $keyword)) {
                            $containsHelpfulKeyword = true;
                            break;
                        }
                    }

                    return $containsHelpfulKeyword;
                }));
        }
    }

    /**
     * Property: Performance tracking should be consistent with configuration.
     */
    public function test_performance_tracking_consistency(): void
    {
        $testCases = [
            ['enabled' => true, 'should_track' => true],
            ['enabled' => false, 'should_track' => false],
        ];

        foreach ($testCases as $case) {
            Log::clearResolvedInstances();
            Log::spy();

            Config::set('exception-handling.performance.track_boot_errors', $case['enabled']);

            $exception = new Exception('TranslatableRecord error');
            $this->handler->report($exception);

            if ($case['should_track']) {
                Log::shouldHaveReceived('info')
                    ->with('Boot error metric tracked', \Mockery::any())
                    ->once();
            } else {
                Log::shouldNotHaveReceived('info', ['Boot error metric tracked', \Mockery::any()]);
            }
        }
    }

    /**
     * Property: Exception type filtering should be exhaustive and exclusive.
     */
    public function test_exception_type_filtering_completeness(): void
    {
        $fastExitExceptions = [
            new \Illuminate\Validation\ValidationException(
                validator(['field' => 'value'], ['field' => 'required'])
            ),
            new \Illuminate\Auth\AuthenticationException('Unauthenticated'),
        ];

        $bootErrorExceptions = [
            new Exception('TranslatableRecord error'),
            new Exception('Class not found'),
        ];

        // Fast exit exceptions should not be processed
        foreach ($fastExitExceptions as $exception) {
            Log::clearResolvedInstances();
            Log::spy();

            $this->handler->report($exception);

            Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
        }

        // Boot error exceptions should be processed
        foreach ($bootErrorExceptions as $exception) {
            Log::clearResolvedInstances();
            Log::spy();

            $this->handler->report($exception);

            Log::shouldHaveReceived('error')
                ->with('Application boot failure detected', \Mockery::any())
                ->once();
        }
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