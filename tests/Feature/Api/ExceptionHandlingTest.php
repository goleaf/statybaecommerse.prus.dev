<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\Feature\TestCase;

final class ExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->group(function (): void {
            Route::get('/testing/authentication-exception', function (): never {
                throw new AuthenticationException();
            });

            Route::get('/testing/authorization-exception', function (): never {
                throw new AuthorizationException();
            });

            Route::get('/testing/validation-exception', function (): never {
                throw ValidationException::withMessages([
                    'email' => ['The email field is required.'],
                ]);
            });

            Route::get('/testing/server-exception', function (): never {
                throw new RuntimeException('Boom');
            });
        });
    }

    public function test_validation_exception_is_rendered_as_problem_details(): void
    {
        $response = $this->getJson('/testing/validation-exception');

        $response
            ->assertStatus(422)
            ->assertJsonPath('type', 'tag:statybaecommerse.prus.dev,2024:error:error.validation')
            ->assertJsonPath('title', 'Validation failed')
            ->assertJsonPath('detail', 'The submitted data was invalid.')
            ->assertJsonPath('error.code', 'error.validation')
            ->assertJsonPath('error.context.email.0', 'The email field is required.');

        $this->assertResponseCarriesCorrelationId($response);
    }

    public function test_authentication_exception_uses_standard_contract(): void
    {
        $response = $this->getJson('/testing/authentication-exception');

        $response
            ->assertStatus(401)
            ->assertJsonPath('type', 'tag:statybaecommerse.prus.dev,2024:error:error.unauthorized')
            ->assertJsonPath('title', 'Unauthorized')
            ->assertJsonPath('detail', 'Authentication is required to access this resource.')
            ->assertJsonPath('error.code', 'error.unauthorized');

        $this->assertResponseCarriesCorrelationId($response);
    }

    public function test_authorization_exception_uses_standard_contract(): void
    {
        $response = $this->getJson('/testing/authorization-exception');

        $response
            ->assertStatus(403)
            ->assertJsonPath('type', 'tag:statybaecommerse.prus.dev,2024:error:error.forbidden')
            ->assertJsonPath('title', 'Forbidden')
            ->assertJsonPath('detail', 'You do not have permission to perform this action.')
            ->assertJsonPath('error.code', 'error.forbidden');

        $this->assertResponseCarriesCorrelationId($response);
    }

    public function test_not_found_exception_is_normalized(): void
    {
        $response = $this->getJson('/testing/route-that-does-not-exist');

        $response
            ->assertStatus(404)
            ->assertJsonPath('type', 'tag:statybaecommerse.prus.dev,2024:error:error.not_found')
            ->assertJsonPath('title', 'Not Found')
            ->assertJsonPath('detail', 'The requested resource could not be located.')
            ->assertJsonPath('error.code', 'error.not_found');

        $this->assertResponseCarriesCorrelationId($response);
    }

    public function test_generic_exception_maps_to_server_error(): void
    {
        $response = $this->getJson('/testing/server-exception');

        $response
            ->assertStatus(500)
            ->assertJsonPath('type', 'tag:statybaecommerse.prus.dev,2024:error:error.server')
            ->assertJsonPath('title', 'Internal Server Error')
            ->assertJsonPath('detail', 'An unexpected error occurred. Please try again later.')
            ->assertJsonPath('error.code', 'error.server');

        $this->assertResponseCarriesCorrelationId($response);
    }

    private function assertResponseCarriesCorrelationId(TestResponse $response): void
    {
        $payload = $response->json();
        $this->assertArrayHasKey('correlation_id', $payload);
        $this->assertIsString($payload['correlation_id']);
        $this->assertNotEmpty($payload['correlation_id']);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertArrayHasKey('timestamp', $payload['meta']);
        $this->assertIsString($payload['meta']['timestamp']);
        $this->assertNotEmpty($payload['meta']['timestamp']);
        $this->assertSame($payload['correlation_id'], $response->headers->get('X-Correlation-ID'));
    }
}
