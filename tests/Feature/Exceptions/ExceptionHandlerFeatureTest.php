<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Feature tests for exception handler HTTP behavior and integration.
 */
class ExceptionHandlerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    // === Boot Error Detection in HTTP Context ===

    public function test_boot_errors_are_detected_during_http_requests(): void
    {
        // Create a route that triggers a boot error
        Route::get('/test-boot-error', function () {
            throw new Exception('TranslatableRecord interface not implemented');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        $this->get('/test-boot-error');

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return $context['error_type'] === 'boot_failure'
                    && str_contains($context['actionable_message'], 'TranslatableRecord');
            }));
    }

    public function test_boot_errors_include_request_context(): void
    {
        Route::get('/test-with-headers', function (Request $request) {
            throw new Exception('Class App\Models\Product not found');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        $this->withHeaders(['X-Request-ID' => 'test-123'])
            ->get('/test-with-headers');

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return isset($context['request_id'])
                    && ($context['request_id'] === 'test-123' || str_contains($context['request_id'], 'req_'));
            }));
    }

    // === API Error Handling ===

    public function test_api_validation_errors_return_structured_response(): void
    {
        Route::post('/api/test', function (Request $request) {
            $request->validate([
                'required_field' => 'required|string',
                'email_field'    => 'required|email',
            ]);
        })->middleware('api');

        $response = $this->postJson('/api/test', [
            'required_field' => '',
            'email_field'    => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'type',
                'title',
                'status',
                'detail',
                'instance',
                'violations' => [
                    '*' => ['field', 'messages', 'reason'],
                ],
            ]);
    }

    public function test_api_type_errors_return_bad_request(): void
    {
        Route::get('/api/test/{id}', function (int $id) {
            return response()->json(['id' => $id]);
        })->middleware('api');

        $response = $this->getJson('/api/test/not-a-number');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'type',
                'title',
                'status',
                'detail',
                'context' => ['reason'],
            ]);
    }

    public function test_api_authentication_errors_return_json(): void
    {
        Route::get('/api/protected', function () {
            throw new AuthenticationException('Token expired');
        })->middleware('api');

        $response = $this->getJson('/api/protected');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Token expired',
            ]);
    }

    // === Web Error Handling ===

    public function test_web_authentication_redirects_to_login(): void
    {
        Route::get('/protected', function () {
            throw new AuthenticationException('Please login');
        })->middleware('web');

        // Mock the login route
        Route::get('/login', function () {
            return 'Login page';
        })->name('login');

        $response = $this->get('/protected');

        $response->assertRedirect('/login');
    }

    public function test_web_authentication_redirects_to_filament_admin_when_available(): void
    {
        Route::get('/admin/protected', function () {
            throw new AuthenticationException('Admin login required');
        })->middleware('web');

        // Mock the Filament admin login route
        Route::get('/admin/login', function () {
            return 'Admin login page';
        })->name('filament.admin.auth.login');

        $response = $this->get('/admin/protected');

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    // === Performance and Rate Limiting ===

    public function test_boot_error_rate_limiting_in_http_context(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        Route::get('/boot-error/{id}', function ($id) {
            throw new Exception("Boot error {$id}");
        });

        // Make requests that trigger boot errors
        for ($i = 1; $i <= 4; $i++) {
            $this->withoutExceptionHandling()
                ->expectException(Exception::class);

            try {
                $this->get("/boot-error/{$i}");
            } catch (Exception) {
                // Expected
            }
        }

        // Only first 2 should be logged due to rate limiting
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any())
            ->twice();
    }

    public function test_exception_handling_performance_within_budget(): void
    {
        Route::get('/performance-test', function () {
            throw new Exception('TranslatableRecord performance test');
        });

        $startTime = microtime(true);

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/performance-test');
        } catch (Exception) {
            // Expected
        }

        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Should complete within performance budget
        $maxTime = config('exception-handling.budgets.exception_handling_max_ms', 5);
        $this->assertLessThan($maxTime, $executionTime,
            "Exception handling took {$executionTime}ms, exceeding budget of {$maxTime}ms");
    }

    // === Security Tests ===

    public function test_sensitive_data_not_exposed_in_api_responses(): void
    {
        Route::post('/api/sensitive', function (Request $request) {
            // Simulate an error that might contain sensitive data
            throw new Exception('Database error: password=secret123, api_key=abc123');
        })->middleware('api');

        $response = $this->withoutExceptionHandling()
            ->postJson('/api/sensitive', []);

        // In production, this would be handled by the exception handler
        // For testing, we verify the handler would sanitize the message
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return ! str_contains($context['message'], 'secret123')
                    && ! str_contains($context['message'], 'abc123');
            }));
    }

    public function test_path_traversal_prevention_in_logging(): void
    {
        // Create an exception with a malicious file path
        $maliciousPath = '/app/../../../etc/passwd';

        Route::get('/path-traversal-test', function () use ($maliciousPath) {
            $exception = new Exception('Path traversal test');

            // Use reflection to set a malicious file path
            $reflection = new ReflectionClass($exception);
            $fileProperty = $reflection->getProperty('file');
            $fileProperty->setAccessible(true);
            $fileProperty->setValue($exception, $maliciousPath);

            throw $exception;
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/path-traversal-test');
        } catch (Exception) {
            // Expected
        }

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::on(function ($context) {
                return ! str_contains($context['file'], '../')
                    && ! str_contains($context['file'], '/etc/passwd');
            }));
    }

    // === Integration Tests ===

    public function test_exception_handler_integrates_with_laravel_exceptions_facade(): void
    {
        Exceptions::fake();

        Route::get('/facade-test', function () {
            throw new Exception('TranslatableRecord facade test');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/facade-test');
        } catch (Exception) {
            // Expected
        }

        // Verify the exception was reported through Laravel's system
        Exceptions::assertReported(Exception::class);

        // And our boot error detection also logged it
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    public function test_multiple_exception_types_handled_correctly_in_sequence(): void
    {
        // Test sequence: ValidationException -> TypeError -> Boot Error

        // 1. Validation Exception (should not trigger boot error detection)
        Route::post('/validation-test', function (Request $request) {
            $request->validate(['required_field' => 'required']);
        });

        $this->postJson('/validation-test', []);

        // 2. Type Error (should log as warning)
        Route::get('/type-error-test/{id}', function (int $id) {
            return response()->json(['id' => $id]);
        });

        $this->getJson('/type-error-test/not-a-number');

        // 3. Boot Error (should trigger boot error detection)
        Route::get('/boot-error-test', function () {
            throw new Exception('TranslatableRecord error');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/boot-error-test');
        } catch (Exception) {
            // Expected
        }

        // Verify correct handling of each exception type
        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
        Log::shouldHaveReceived('warning')
            ->with('Type error triggered by request parameters.', \Mockery::any());
        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }

    // === Configuration Integration Tests ===

    public function test_configuration_changes_affect_http_behavior(): void
    {
        // Disable boot error detection
        Config::set('exception-handling.boot_error_detection.enabled', false);

        Route::get('/config-test', function () {
            throw new Exception('TranslatableRecord config test');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/config-test');
        } catch (Exception) {
            // Expected
        }

        // Should not log boot error when disabled
        Log::shouldNotHaveReceived('error', ['Application boot failure detected', \Mockery::any()]);
    }

    public function test_custom_log_channel_used_in_http_context(): void
    {
        Config::set('exception-handling.boot_error_detection.log_channel', 'custom');

        // Mock the custom channel
        Log::shouldReceive('channel')
            ->with('custom')
            ->andReturnSelf();

        Log::shouldReceive('critical')
            ->with('Boot failure', \Mockery::any());

        Route::get('/custom-channel-test', function () {
            throw new Exception('TranslatableRecord custom channel test');
        });

        $this->withoutExceptionHandling()
            ->expectException(Exception::class);

        try {
            $this->get('/custom-channel-test');
        } catch (Exception) {
            // Expected
        }

        Log::shouldHaveReceived('error')
            ->with('Application boot failure detected', \Mockery::any());
    }
}
