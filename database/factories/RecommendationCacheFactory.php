<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * @extends Factory<RecommendationCache>
 */
class RecommendationCacheFactory extends Factory
{
    protected $model = RecommendationCache::class;

    public function definition(): array
    {
        $state = [
            'cache_key'       => $this->faker->unique()->uuid(),
            'block_id'        => RecommendationBlock::factory(),
            'user_id'         => User::factory(),
            'product_id'      => Product::factory(),
            'context_type'    => $this->faker->randomElement(['homepage', 'category', 'product']),
            'context_data'    => ['category' => $this->faker->word()],
            'recommendations' => [
                ['product_id' => Product::factory()->create()->id, 'score' => $this->faker->randomFloat(2, 0, 1)],
            ],
            'hit_count'  => 0,
            'expires_at' => now()->addHours(24),
        ];

        // Match the active schema so SQLite snapshots without the meta column
        // continue to operate when the factory creates cache rows for tests.
        if ($this->tableHasColumn('recommendation_cache', 'meta')) {
            $state['meta'] = [];
        }

        return $state;
    }

    /**
     * Verify column availability at runtime before including optional payload
     * fragments that are absent from pared-down database snapshots.
     */
    private function tableHasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
