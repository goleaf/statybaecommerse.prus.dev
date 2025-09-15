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
            'product_id' => Product::factory(),
            'similar_product_id' => Product::factory(),
            'algorithm_type' => $this->faker->randomElement(['cosine_similarity', 'euclidean_distance', 'jaccard_index']),
            'similarity_score' => $this->faker->randomFloat(6, 0, 1),
            'calculation_data' => [
                'features' => $this->faker->randomElements(['color', 'size', 'brand', 'category', 'price'], 3),
                'weights' => $this->faker->randomElements([0.2, 0.3, 0.4, 0.5], 3),
            ],
            'calculated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function highSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(6, 0.8, 1.0),
        ]);
    }

    public function mediumSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(6, 0.5, 0.8),
        ]);
    }

    public function lowSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(6, 0.0, 0.5),
        ]);
    }

    public function cosineSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'algorithm_type' => 'cosine_similarity',
        ]);
    }

    public function euclideanDistance(): static
    {
        return $this->state(fn (array $attributes) => [
            'algorithm_type' => 'euclidean_distance',
        ]);
    }

    public function recentlyCalculated(): static
    {
        return $this->state(fn (array $attributes) => [
            'calculated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
