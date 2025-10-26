<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApiKeyScope;
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
        $cases = ApiKeyScope::cases();

        $scopes = collect($cases)
            ->shuffle()
            ->take(random_int(1, count($cases)))
            ->map(static fn (ApiKeyScope $scope): string => $scope->value)
            ->values()
            ->all();

        $credentials = ApiKey::generateCredentials();

        return [
            'name'         => sprintf('%s API Access', $this->faker->company()),
            'key'          => $credentials['hashed'],
            'secret'       => ApiKey::generatePlainTextSecret(),
            'scopes'       => array_values($scopes),
            'permissions'  => null,
            'rate_limit'   => $this->faker->numberBetween(100, 1000),
            'is_active'    => true,
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
