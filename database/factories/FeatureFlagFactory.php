<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeatureFlag>
 */
class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'key' => fake()->unique()->slug(),
            'description' => fake()->sentence(10),
            'is_active' => fake()->boolean(70),
            'is_enabled' => fake()->boolean(30),
            'is_global' => fake()->boolean(20),
            'environment' => fake()->randomElement(['local', 'staging', 'production', null]),
            'category' => fake()->randomElement(['ui', 'performance', 'security', 'analytics', 'payment', 'shipping']),
            'priority' => fake()->numberBetween(0, 100),
            'conditions' => [
                'user_type' => fake()->randomElement(['admin', 'customer', 'guest']),
                'country' => fake()->countryCode(),
            ],
            'starts_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->optional(0.5)->dateTimeBetween('+1 month', '+3 months'),
            'metadata' => [
                'version' => fake()->semver(),
                'team' => fake()->randomElement(['frontend', 'backend', 'devops', 'qa']),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global' => true,
        ]);
    }

    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => 'production',
        ]);
    }

    public function staging(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => 'staging',
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => 'local',
        ]);
    }

    public function ui(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'ui',
        ]);
    }

    public function performance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'performance',
        ]);
    }

    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'security',
        ]);
    }
}