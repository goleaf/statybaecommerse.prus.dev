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

        $credentials = ApiKey::generateCredentials();

        return [
            'key' => $credentials['hashed'],
            'name' => sprintf('%s API Access', $this->faker->company()),
            'scopes' => array_values($scopes),
            'rate_limit' => $this->faker->numberBetween(100, 1000),
            'active' => true,
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
