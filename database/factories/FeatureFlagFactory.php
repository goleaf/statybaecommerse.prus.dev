<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

final class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'key' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => false,
            'conditions' => null,
            'rollout_percentage' => null,
            'environment' => null,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'metadata' => null,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'category' => $this->faker->randomElement(['checkout', 'payment', 'ux', 'performance']),
            'impact_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'rollout_strategy' => $this->faker->randomElement(['gradual', 'immediate', 'canary']),
            'rollback_plan' => $this->faker->sentence(),
            'success_metrics' => null,
            'approval_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approval_notes' => $this->faker->sentence(),
            'created_by' => $this->faker->name(),
            'updated_by' => $this->faker->name(),
            'last_activated' => now()->subDays(1),
            'last_deactivated' => null,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global' => true,
        ]);
    }

    public function withConditions(): static
    {
        return $this->state(fn (array $attributes) => [
            'conditions' => [
                'user_type' => 'premium',
                'country' => 'LT',
                'browser' => 'chrome',
            ],
        ]);
    }

    public function withMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'owner' => 'development_team',
                'priority' => 'high',
                'tags' => ['checkout', 'payment', 'ux'],
            ],
        ]);
    }

    public function withSuccessMetrics(): static
    {
        return $this->state(fn (array $attributes) => [
            'success_metrics' => [
                'conversion_rate' => 'increase',
                'checkout_time' => 'decrease',
                'user_satisfaction' => 'increase',
            ],
        ]);
    }
}

