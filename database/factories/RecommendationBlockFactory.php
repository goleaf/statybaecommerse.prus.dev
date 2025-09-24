<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecommendationBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecommendationBlock>
 */
final class RecommendationBlockFactory extends Factory
{
    protected $model = RecommendationBlock::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'config_ids' => [],
            'is_active' => $this->faker->boolean(90),
            'max_products' => $this->faker->numberBetween(1, 12),
            'cache_duration' => $this->faker->numberBetween(60, 86400),
            'display_settings' => [
                'layout' => $this->faker->randomElement(['grid', 'list']),
                'columns' => $this->faker->numberBetween(2, 6),
            ],
        ];
    }
}
