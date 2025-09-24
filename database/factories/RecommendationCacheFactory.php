<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecommendationCache>
 */
class RecommendationCacheFactory extends Factory
{
    protected $model = RecommendationCache::class;

    public function definition(): array
    {
        return [
            'cache_key' => $this->faker->unique()->uuid(),
            'block_id' => RecommendationBlock::factory(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'context_type' => $this->faker->randomElement(['homepage', 'category', 'product']),
            'context_data' => ['category' => $this->faker->word()],
            'recommendations' => [
                ['product_id' => Product::factory()->create()->id, 'score' => $this->faker->randomFloat(2, 0, 1)],
            ],
            'hit_count' => 0,
            'expires_at' => now()->addHours(24),
        ];
    }
}
