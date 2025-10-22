<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\Feature\TestCase;

final class ExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->group(function (): void {
            Route::get('/testing/exceptions/server', function (): void {
                throw new RuntimeException('Unexpected failure');
            });

            Route::get('/testing/exceptions/not-found', function (): void {
                abort(404, 'Record not found');
            });

            Route::get('/testing/exceptions/authentication', function (): void {
                throw new AuthenticationException('Authentication required.');
            });

            Route::get('/testing/exceptions/authorization', function (): void {
                throw new AuthorizationException('Missing [system.autocomplete] ability.');
            });

            Route::get('/testing/exceptions/rate-limited', function (): void {
                // Simulate the framework's throttle middleware raising an HTTP 429.
                abort(429, 'Too many requests, please try again later.');
            });

            Route::post('/testing/exceptions/validation', function (Request $request) {
                $request->validate([
                    'model_class' => ['required', 'string'],
                ]);

                return response()->json(['ok' => true]);
            });
        });
    }

    public function test_unhandled_exception_returns_problem_details(): void
    {
        $response = $this->getJson('/testing/exceptions/server');

        $response
            ->assertStatus(500)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::SERVER_ERROR))
            ->assertJsonPath('title', ErrorCodes::describe(ErrorCodes::SERVER_ERROR))
            ->assertJsonPath('error.code', ErrorCodes::SERVER_ERROR)
            ->assertJsonStructure([
                'correlation' => ['trace_id', 'correlation_id'],
                'meta'        => ['locale', 'timestamp'],
            ]);
    }

    public function test_validation_exception_returns_structured_violations(): void
    {
        $response = $this->postJson('/testing/exceptions/validation', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::VALIDATION_FAILED))
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonPath('error.context.violations.0.field', 'model_class')
            ->assertJsonPath('error.context.violations.0.reason', 'The model class field is required.');
    }

    public function test_authentication_exception_uses_shared_error_code(): void
    {
        $response = $this->getJson('/testing/exceptions/authentication');

        $response
            ->assertStatus(401)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::UNAUTHORIZED))
            ->assertJsonPath('error.code', ErrorCodes::UNAUTHORIZED)
            ->assertJsonPath('detail', 'Authentication required.');
    }

    public function test_authorization_exception_uses_shared_error_code(): void
    {
        $response = $this->getJson('/testing/exceptions/authorization');

        $response
            ->assertStatus(403)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::FORBIDDEN))
            ->assertJsonPath('error.code', ErrorCodes::FORBIDDEN)
            ->assertJsonPath('error.context.reason', 'Missing [system.autocomplete] ability.');
    }

    public function test_http_exception_maps_to_not_found_error_code(): void
    {
        $response = $this->getJson('/testing/exceptions/not-found');

        $response
            ->assertStatus(404)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::NOT_FOUND))
            ->assertJsonPath('error.code', ErrorCodes::NOT_FOUND)
            ->assertJsonPath('detail', 'Record not found');
    }

    public function test_too_many_requests_exception_maps_to_rate_limited_code(): void
    {
        $response = $this->getJson('/testing/exceptions/rate-limited');

        $response
            ->assertStatus(429)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::RATE_LIMITED))
            ->assertJsonPath('error.code', ErrorCodes::RATE_LIMITED)
            ->assertJsonPath('detail', 'Too many requests, please try again later.');
    }
}
