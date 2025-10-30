<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\AutocompleteController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class AutocompleteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_returns_service_payload(): void
    {
        $controller = app(AutocompleteController::class);
        $request = Request::create('/api/autocomplete/search', 'GET', [
            'q'     => ' query ',
            'limit' => 15,
            'types' => ['products', 'brands'],
        ]);

        $response = $controller->search($request);
        $payload = $response->getData(true);

        // Assert the controller emits HTTP 200 so frontend widgets receive a successful signal.
        $this->assertSame(200, $response->getStatusCode());
        // Assert the JSON payload marks the request as successful even when no records match the query.
        $this->assertTrue($payload['success']);
        // Assert the meta data echoes the normalised search query for downstream analytics.
        $this->assertSame('query', $payload['meta']['query']);
        // Assert the limit value is preserved so client-side pagination can mirror server-side behaviour.
        $this->assertSame(15, $payload['meta']['limit']);
        // Assert the types array is lowercased and trimmed before the service executes.
        $this->assertSame(['products', 'brands'], $payload['meta']['types']);
    }

    public function test_search_endpoint_returns_validation_errors_for_missing_query(): void
    {
        $controller = app(AutocompleteController::class);
        $request = Request::create('/api/autocomplete/search', 'GET', [
            'q'     => '   ',
            'types' => ['products'],
        ]);

        $response = $controller->search($request);
        $payload = $response->getData(true);

        // Assert blank queries fail validation with HTTP 422 so clients can surface inline errors.
        $this->assertSame(422, $response->getStatusCode());
        // Assert the response contains the validation structure used by the legacy frontend.
        $this->assertSame('Validation failed.', $payload['message']);
        // Assert the error bag specifically calls out the query parameter for user guidance.
        $this->assertArrayHasKey('q', $payload['errors']);
    }
}
