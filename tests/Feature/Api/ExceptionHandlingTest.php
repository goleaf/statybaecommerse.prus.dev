<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

final class ExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->prefix('api/test-exceptions')->group(function (): void {
            Route::post('/validation', static function (): void {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['The email field is required.'],
                ]);
            })->name('api.test.validation');

            Route::get('/authentication', static function (): void {
                throw new AuthenticationException('Unauthenticated.');
            })->name('api.test.authentication');

            Route::get('/authorization', static function (): void {
                throw new AuthorizationException('Forbidden action.');
            })->name('api.test.authorization');

            Route::get('/not-found', static function (): void {
                $exception = new ModelNotFoundException();
                $exception->setModel('App\\Models\\User');

                throw $exception;
            })->name('api.test.not-found');

            Route::get('/rate-limit', static function (): void {
                throw new TooManyRequestsHttpException(60, 'Too many requests.');
            })->name('api.test.rate-limit');

            Route::get('/server-error', static function (): void {
                throw new DomainException('Domain failure.');
            })->name('api.test.server');

            Route::get('/unexpected', static function (): void {
                throw new \RuntimeException('Unexpected failure.');
            })->name('api.test.unexpected');
        });
    }

    public function test_validation_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->postJson('/api/test-exceptions/validation');

        $response->assertStatus(422);
        $this->assertProblemJson($response, 'validation_error');
        $response->assertJsonPath('error.details.errors.email.0', 'The email field is required.');
    }

    public function test_authentication_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/authentication');

        $response->assertStatus(401);
        $this->assertProblemJson($response, 'unauthenticated');
    }

    public function test_authorization_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/authorization');

        $response->assertStatus(403);
        $this->assertProblemJson($response, 'forbidden');
    }

    public function test_not_found_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/not-found');

        $response->assertStatus(404);
        $this->assertProblemJson($response, 'resource_not_found');
    }

    public function test_rate_limited_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/rate-limit');

        $response->assertStatus(429);
        $this->assertProblemJson($response, 'rate_limited');
        $this->assertSame(60, $response->json('error.details.retry_after'));
    }

    public function test_domain_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/server-error');

        $response->assertStatus(400);
        $this->assertProblemJson($response, 'domain_error');
    }

    public function test_unhandled_exceptions_are_rendered_as_problem_json(): void
    {
        $response = $this->getJson('/api/test-exceptions/unexpected');

        $response->assertStatus(500);
        $this->assertProblemJson($response, 'server_error');
    }

    private function assertProblemJson(TestResponse $response, string $expectedCode): void
    {
        $response->assertHeader('Content-Type', 'application/problem+json');

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('error', fn (AssertableJson $error) => $error
                ->where('code', $expectedCode)
                ->whereType('message', 'string')
                ->where('details', fn ($value) => is_array($value) || $value === null)
                ->where('correlation_id', fn ($value) => is_string($value) && preg_match('/^[0-9a-fA-F-]{36}$/', $value) === 1)
            )
        );
    }
}
