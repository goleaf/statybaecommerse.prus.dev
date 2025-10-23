<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiKey>
 */
final class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $credentials = ApiKey::generateCredentials();

        return [
            'name' => sprintf('%s API Access', $this->faker->company()),
            'key' => $credentials['hashed'],
            'secret' => ApiKey::generatePlainTextSecret(),
            // Leave scopes and permissions empty by default so tests can opt in to whichever convention they require.
            'scopes' => null,
            'permissions' => null,
            // Default to no explicit rate limit so configuration values drive the throttle behaviour unless tests override it.
            'rate_limit' => null,
            'is_active' => true,
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (): array => [
            'rate_limit' => null,
        ]);
    }
}
