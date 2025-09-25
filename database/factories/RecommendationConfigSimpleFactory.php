<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecommendationConfigSimple;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RecommendationConfigSimple>
 */
final class RecommendationConfigSimpleFactory extends Factory
{
    protected $model = RecommendationConfigSimple::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'code' => $this->faker->unique()->slug(2),
            'description' => $this->faker->optional()->sentence(8),
            'algorithm_type' => $this->faker->randomElement([
                'collaborative',
                'content_based',
                'hybrid',
                'popularity',
                'trending',
                'similarity',
                'custom',
            ]),
            'min_score' => $this->faker->randomFloat(6, 0, 1),
            'max_results' => $this->faker->numberBetween(1, 100),
            'decay_factor' => $this->faker->randomFloat(6, 0, 1),
            'exclude_out_of_stock' => $this->faker->boolean(),
            'exclude_inactive' => $this->faker->boolean(),
            'price_weight' => $this->faker->randomFloat(6, 0, 1),
            'rating_weight' => $this->faker->randomFloat(6, 0, 1),
            'popularity_weight' => $this->faker->randomFloat(6, 0, 1),
            'recency_weight' => $this->faker->randomFloat(6, 0, 1),
            'category_weight' => $this->faker->randomFloat(6, 0, 1),
            'custom_weight' => $this->faker->randomFloat(6, 0, 1),
            'cache_duration' => $this->faker->numberBetween(1, 1440),
            'is_active' => $this->faker->boolean(),
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'notes' => $this->faker->optional()->sentence(12),
        ];
    }
}

