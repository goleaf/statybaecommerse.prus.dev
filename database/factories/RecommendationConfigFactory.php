<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecommendationConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RecommendationConfig>
 */
final class RecommendationConfigFactory extends Factory
{
    protected $model = RecommendationConfig::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(),
            'type' => $this->faker->randomElement([
                'collaborative',
                'content_based',
                'hybrid',
                'popularity',
                'trending',
                'cross_sell',
                'up_sell',
            ]),
            'description' => $this->faker->sentence(),
            'min_score' => $this->faker->randomFloat(2, 0, 1),
            'max_results' => $this->faker->numberBetween(5, 50),
            'decay_factor' => $this->faker->randomFloat(2, 0, 1),
            'priority' => $this->faker->numberBetween(0, 10),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
            'is_default' => false,
            'cache_ttl' => $this->faker->numberBetween(30, 3600),
            'config' => [],
            'filters' => [],
            'conditions' => [],
            'metadata' => [],
            'enable_caching' => $this->faker->boolean(),
            'enable_analytics' => $this->faker->boolean(),
            'batch_size' => $this->faker->numberBetween(10, 100),
            'timeout_seconds' => $this->faker->numberBetween(5, 60),
        ];
    }
}
