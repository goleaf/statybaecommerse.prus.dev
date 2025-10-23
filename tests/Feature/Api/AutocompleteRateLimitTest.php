<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\PreparesRateLimitTestDatabase;
use Tests\TestCase;

final class AutocompleteRateLimitTest extends TestCase
{
    use PreparesRateLimitTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRateLimitTestEnvironment();
    }

    public function test_autocomplete_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create();

        $originalLimit = config('api.rate_limits.autocomplete');
        config(['api.rate_limits.autocomplete' => 1]);
        RateLimiter::clear('user:'.$user->getKey().'|autocomplete');

        Sanctum::actingAs($user, ['system.autocomplete']);

        $payload = [
            'model_class' => User::class,
            'search_field' => 'email',
            'label_field' => 'email',
            'value_field' => 'id',
            'search_query' => (string) $user->email,
        ];

        try {
            $this->postJson(route('api.v1.autocomplete.search'), $payload)->assertOk();
            $this->postJson(route('api.v1.autocomplete.search'), $payload)->assertStatus(429);
        } finally {
            config(['api.rate_limits.autocomplete' => $originalLimit]);
            RateLimiter::clear('user:'.$user->getKey().'|autocomplete');
        }
    }
}
