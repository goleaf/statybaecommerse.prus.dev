<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecommendationAnalytics>
 */
final class RecommendationAnalyticsFactory extends Factory
{
    protected $model = RecommendationAnalytics::class;

    public function definition(): array
    {
        return [
            'block_id' => RecommendationBlock::factory(),
            'config_id' => RecommendationConfig::factory(),
            'user_id' => User::factory(),
            'product_id' => null,
            'action' => $this->faker->randomElement(['view', 'click', 'purchase']),
            'ctr' => $this->faker->randomFloat(4, 0, 1),
            'conversion_rate' => $this->faker->randomFloat(4, 0, 1),
            'metrics' => [
                'impressions' => $this->faker->numberBetween(1, 1000),
                'clicks' => $this->faker->numberBetween(0, 500),
            ],
            'date' => $this->faker->date(),
        ];
    }
}
