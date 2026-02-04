<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSimilarity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductSimilarity>
 */
final class ProductSimilarityFactory extends Factory
{
    protected $model = ProductSimilarity::class;

    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),
            'similar_product_id' => Product::factory(),
            'calculation_data'   => [
                'features' => $this->faker->randomElements(['color', 'size', 'brand', 'category', 'price'], 3),
                'weights'  => $this->faker->randomElements([0.2, 0.3, 0.4, 0.5], 3),
            ],
            'calculated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function recentlyCalculated(): static
    {
        return $this->state(fn (array $attributes) => [
            'calculated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
