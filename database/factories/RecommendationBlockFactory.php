<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecommendationBlock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * @extends Factory<RecommendationBlock>
 */
final class RecommendationBlockFactory extends Factory
{
    protected $model = RecommendationBlock::class;

    public function definition(): array
    {
        $state = [
            'name'             => $this->faker->unique()->slug(),
            'title'            => $this->faker->sentence(3),
            'description'      => $this->faker->optional()->paragraph(),
            'config_ids'       => [],
            'is_active'        => $this->faker->boolean(90),
            'max_products'     => $this->faker->numberBetween(1, 12),
            'cache_duration'   => $this->faker->numberBetween(60, 86400),
            'display_settings' => [
                'layout'  => $this->faker->randomElement(['grid', 'list']),
                'columns' => $this->faker->numberBetween(2, 6),
            ],
        ];

        // Guard optional columns so SQLite-driven suites avoid touching fields that
        // have not landed in the lightweight schema snapshots yet.
        if ($this->tableHasColumn('recommendation_blocks', 'meta')) {
            $state['meta'] = [];
        }

        return $state;
    }

    /**
     * Determine if the current connection exposes the requested column before
     * trying to include it in the factory payload.
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
