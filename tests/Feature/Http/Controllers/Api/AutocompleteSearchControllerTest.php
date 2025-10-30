<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AutocompleteSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_autocomplete_search_returns_trimmed_results(): void
    {
        $actor = User::factory()->create();
        $matchingUser = User::factory()->create([
            'email' => 'searchable@example.com',
            'name'  => 'Searchable User',
        ]);
        User::factory()->create([
            'email' => 'nonmatch@example.com',
            'name'  => 'Hidden User',
        ]);

        Sanctum::actingAs($actor, ['system.autocomplete']);

        $response = $this->postJson(route('api.v1.autocomplete.search'), [
            'model_class'  => User::class,
            'search_field' => 'email',
            'label_field'  => 'email',
            'value_field'  => 'id',
            'search_query' => '  searchable@example.com  ',
            'limit'        => 5,
        ]);

        // Assert the controller responds with HTTP 200 so consumers know the lookup succeeded.
        $response->assertOk();
        // Assert the payload marks the request as successful so UI components can proceed confidently.
        $response->assertJsonPath('success', true);

        $results = $response->json('results');

        // Assert exactly one record matched the trimmed query to avoid leaking unrelated users.
        $this->assertCount(1, $results);
        // Assert the matched record exposes the configured identifier so selections submit stable keys.
        $this->assertSame($matchingUser->getKey(), $results[0]['value']);
        // Assert the label mirrors the requested column so dropdown text renders the expected email.
        $this->assertSame('searchable@example.com', $results[0]['label']);
    }

    public function test_autocomplete_search_returns_empty_array_for_blank_terms(): void
    {
        $actor = User::factory()->create();
        Sanctum::actingAs($actor, ['system.autocomplete']);

        $response = $this->postJson(route('api.v1.autocomplete.search'), [
            'model_class'  => User::class,
            'search_field' => 'email',
            'label_field'  => 'email',
            'value_field'  => 'id',
            'search_query' => '   ',
        ]);

        // Assert whitespace-only queries trigger validation so callers know to provide meaningful input.
        $response->assertStatus(422);
        // Assert the validation payload references the search_query field for targeted UI messaging.
        $this->assertNotEmpty($response->json('errors.search_query.0'));
    }

    public function test_autocomplete_search_returns_validation_error_for_unknown_column(): void
    {
        $actor = User::factory()->create();
        Sanctum::actingAs($actor, ['system.autocomplete']);

        $response = $this->postJson(route('api.v1.autocomplete.search'), [
            'model_class'  => User::class,
            'search_field' => 'does_not_exist',
            'label_field'  => 'email',
            'value_field'  => 'id',
            'search_query' => 'search',
        ]);

        // Assert invalid configuration returns HTTP 422 so integrators can correct their payload.
        $response->assertStatus(422);
        // Assert the validation response highlights the offending field for precise debugging.
        $violations = $response->json('error.context.violations');
        // Assert the contract returns a violation list so clients can display inline field errors.
        $this->assertIsArray($violations);
        // Assert the violation entry targets the search_field attribute for precise highlighting.
        $this->assertSame('search_field', $violations[0]['field']);
        // Assert the violation reason mirrors the human-readable message used in the OpenAPI spec.
        $this->assertSame(
            'The requested column does_not_exist is not available for autocomplete searches.',
            $violations[0]['reason']
        );
    }
}
