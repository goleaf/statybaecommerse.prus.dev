<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
final class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $availableScopes = [
            'orders.read',
            'orders.write',
            'products.read',
            'products.write',
            'customers.read',
            'customers.write',
            'analytics.read',
        ];

        $scopes = $this->faker->randomElements($availableScopes, $this->faker->numberBetween(1, 3));

        return [
            'key' => Str::upper(bin2hex(random_bytes(16))),
            'name' => sprintf('%s API Access', $this->faker->company()),
            'scopes' => array_values($scopes),
            'rate_limit' => $this->faker->numberBetween(100, 1000),
            'active' => true,
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the API key is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the API key has no rate limit restrictions.
     */
    public function unlimited(): static
    {
        return $this->state(fn () => [
            'rate_limit' => null,
        ]);
    }
}
