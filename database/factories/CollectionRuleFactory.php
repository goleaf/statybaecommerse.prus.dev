<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collection;
use App\Models\CollectionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CollectionRule>
 */
class CollectionRuleFactory extends Factory
{
    protected $model = CollectionRule::class;

    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'field' => $this->faker->randomElement(['category_id', 'brand_id', 'price', 'status']),
            'operator' => $this->faker->randomElement(['equals', 'not_equals', 'greater_than', 'less_than', 'contains']),
            'value' => $this->faker->word(),
            'position' => $this->faker->numberBetween(0, 10),
        ];
    }
}
