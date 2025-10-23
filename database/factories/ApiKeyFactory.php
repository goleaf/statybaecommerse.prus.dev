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

        return [
            'name' => $this->faker->unique()->words(2, true),
            'key' => ApiKey::generatePlainTextKey(),
            'secret' => ApiKey::generatePlainTextSecret(),
            'permissions' => $scopes,
            'rate_limits' => ['default' => random_int(60, 600)],
            'is_active' => true,
            'last_used_at' => now()->subMinutes(random_int(1, 1440)),
        ];
    }
}
