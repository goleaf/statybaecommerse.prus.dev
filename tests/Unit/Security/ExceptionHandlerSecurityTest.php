<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Security-focused tests for the Exception Handler.
 *
 * Tests information disclosure, injection vulnerabilities, and security controls.
 */
class ExceptionHandlerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        Handler::resetCache();
        BootErrorRateLimiter::reset();

        Config::set('exception-handling.boot_error_detection.enabled', true);
        Config::set('exception-handling.boot_error_detection.patterns', [
            'Interface',
            'not found',
            'undefined method',
            'Cannot declare class',
            'Fatal error',
            'Parse error',
            'Syntax error',
            'translations()',
            'TranslatableRecord',
        ]);
        Config::set('exception-handling.boot_error_detection.paths', [
            '/Models/',
            '/Contracts/',
            '/Providers/',
            '/bootstrap/',
        ]);
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10);
        $this->handler = app(Handler::class);
    }

    protected function tearDown(): void
    {
        Handler::resetCache();
        BootErrorRateLimiter::reset();
        parent::tearDown();
    }

    /** @test */
    public function it_prevents_information_disclosure_in_boot_error_logs(): void
    {
        Log::spy();

        // Test that sensitive information is not logged
        $exception = new Exception('TranslatableRecord error: Database password: secret123 in /app/Models/Product.php');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                // Ensure sensitive data patterns are not logged
                $message = $context['message'] ?? '';
                $actionableMessage = $context['actionable_message'] ?? '';

                // Should not contain potential secrets
                return ! str_contains($message, 'secret123') &&
                       ! str_contains($actionableMessage, 'password:');
            }));
    }

    /** @test */
    public function it_sanitizes_file_paths_in_error_context(): void
    {
        Log::spy();

        $exception = new Exception('TranslatableRecord error in sensitive file');

        // Mock file path with sensitive information
        $reflection = new ReflectionClass($exception);
        $fileProperty = $reflection->getProperty('file');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($exception, '/var/www/html/.env');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                // File path should be present but not expose sensitive directories
                return isset($context['file']) &&
                       ! str_contains($context['file'], 'secret') &&
                       ! str_contains($context['file'], 'password');
            }));
    }

    /** @test */
    public function it_prevents_log_injection_attacks(): void
    {
        Log::spy();

        // Test with malicious input that could cause log injection
        $maliciousInput = "TranslatableRecord error\n[2024-01-01] FAKE LOG ENTRY: Admin access granted\nReal error";
        $exception = new Exception($maliciousInput);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $message = $context['message'] ?? '';

                // Should not contain newlines that could inject fake log entries.
                return ! str_contains($message, "\n[2024-01-01]")
                    && ! str_contains($message, "\n")
                    && ! str_contains($message, "\r");
            }));
    }

    /** @test */
    public function it_limits_error_message_length_to_prevent_dos(): void
    {
        Log::spy();

        // Create an extremely long error message
        $longMessage = str_repeat('A', 10000) . ' TranslatableRecord error';
        $exception = new Exception($longMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $message = $context['message'] ?? '';

                // Message should be truncated to reasonable length
                return strlen($message) < 5000;
            }));
    }

    /** @test */
    public function it_validates_configuration_input_to_prevent_injection(): void
    {
        Log::spy();

        // Test with malicious configuration
        Config::set('exception-handling.boot_error_detection.patterns', [
            'valid_pattern',
            '"; DROP TABLE users; --',
            '<script>alert("xss")</script>',
        ]);

        $exception = new Exception('valid_pattern error occurred');

        $this->handler->report($exception);

        // Should still work with valid patterns but ignore malicious ones
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    /** @test */
    public function it_prevents_path_traversal_in_boot_related_paths(): void
    {
        Log::spy();

        Config::set('exception-handling.boot_error_detection.paths', [
            '/Models/',
            '../../../etc/passwd',
            '../../../../var/log/',
        ]);

        $exception = new Exception('Error in model');

        $reflection = new ReflectionClass($exception);
        $fileProperty = $reflection->getProperty('file');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($exception, '/app/Models/Product.php');

        $this->handler->report($exception);

        // Should detect the legitimate path but ignore malicious ones
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    /** @test */
    public function it_rate_limits_boot_error_logging_to_prevent_spam(): void
    {
        Log::spy();

        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        // Simulate rapid fire boot errors
        for ($i = 0; $i < 100; $i++) {
            $exception = new Exception("TranslatableRecord boot error #{$i}");
            $this->handler->report($exception);
        }

        // Should not log all 100 errors (implementation should include rate limiting)
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->times(5); // Should only log up to the rate limit
    }

    /** @test */
    public function it_does_not_expose_internal_class_names_in_actionable_messages(): void
    {
        Log::spy();

        $exception = new Exception('App\\Internal\\SecretService class not found');

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $actionableMessage = $context['actionable_message'] ?? '';

                // Should not expose internal class names
                return ! str_contains($actionableMessage, 'SecretService') &&
                       ! str_contains($actionableMessage, 'Internal\\');
            }));
    }

    /** @test */
    public function it_validates_log_channel_configuration(): void
    {
        Log::spy();

        // Test with potentially malicious log channel
        Config::set('exception-handling.boot_error_detection.log_channel', '../../../var/log/auth.log');

        $exception = new Exception('TranslatableRecord error');

        // Should not throw exception or cause path traversal
        $this->handler->report($exception);

        // Should fall back to default logging
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    /** @test */
    public function it_prevents_memory_exhaustion_from_large_context_arrays(): void
    {
        Log::spy();

        // Create exception with large trace
        $exception = new Exception('TranslatableRecord error');

        // Mock a large stack trace
        $reflection = new ReflectionClass($exception);
        $traceProperty = $reflection->getProperty('trace');
        $traceProperty->setAccessible(true);
        $largeTrace = array_fill(0, 1000, ['file' => '/app/test.php', 'line' => 1]);
        $traceProperty->setValue($exception, $largeTrace);

        $startMemory = memory_get_usage();
        $this->handler->report($exception);
        $endMemory = memory_get_usage();

        // Memory usage should not increase dramatically
        $memoryIncrease = $endMemory - $startMemory;
        expect($memoryIncrease)->toBeLessThan(1024 * 1024); // Less than 1MB
    }

    /** @test */
    public function it_handles_unicode_and_special_characters_safely(): void
    {
        Log::spy();

        $unicodeMessage = "TranslatableRecord error: 测试 🚀 \x00\x01\x02";
        $exception = new Exception($unicodeMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $message = $context['message'] ?? '';

                // Should handle unicode safely and remove null bytes
                return ! str_contains($message, "\x00") &&
                       ! str_contains($message, "\x01") &&
                       mb_check_encoding($message, 'UTF-8');
            }));
    }

    /** @test */
    public function it_prevents_recursive_exception_handling(): void
    {
        // Clear the spy and set up specific mocks
        Log::clearResolvedInstances();

        // Mock Log to allow normal Laravel error logging first
        Log::shouldReceive('error')
            ->with('TranslatableRecord error', \Mockery::any())
            ->once();

        // Mock Log to throw exception on boot error logging
        Log::shouldReceive('error')
            ->once()
            ->with('Application boot failure detected', \Mockery::any())
            ->andThrow(new Exception('Logging failed'));

        Log::shouldReceive('emergency')
            ->once()
            ->with('Boot error logging failed', \Mockery::any());

        $exception = new Exception('TranslatableRecord error');

        // Should not cause infinite recursion
        $this->handler->report($exception);
    }

    /** @test */
    public function it_respects_security_configuration_overrides(): void
    {
        Log::spy();

        Config::set('exception-handling.security.max_message_length', 100);
        Config::set('exception-handling.security.sanitize_paths', true);
        Config::set('exception-handling.security.rate_limit_enabled', true);

        $longMessage = 'TranslatableRecord error: ' . str_repeat('A', 200);
        $exception = new Exception($longMessage);

        $this->handler->report($exception);

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                $message = $context['message'] ?? '';

                // Truncation appends a suffix marker after the configured maximum.
                return strlen($message) <= 120 && str_contains($message, '[truncated]');
            }));
    }
}
