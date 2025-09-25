<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAnalytics>
 */
final class ProductAnalyticsFactory extends Factory
{
    protected $model = ProductAnalytics::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'views' => fake()->numberBetween(0, 10000),
            'clicks' => fake()->numberBetween(0, 1000),
            'conversions' => fake()->numberBetween(0, 100),
            'revenue' => fake()->randomFloat(2, 0, 50000),
            'bounce_rate' => fake()->randomFloat(2, 0, 100),
            'avg_time_on_page' => fake()->numberBetween(30, 600),
            'search_impressions' => fake()->numberBetween(0, 5000),
            'search_clicks' => fake()->numberBetween(0, 500),
            'cart_additions' => fake()->numberBetween(0, 200),
            'wishlist_additions' => fake()->numberBetween(0, 150),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function withHighPerformance(): static
    {
        return $this->state(fn(array $attributes) => [
            'views' => fake()->numberBetween(5000, 20000),
            'clicks' => fake()->numberBetween(500, 2000),
            'conversions' => fake()->numberBetween(50, 300),
            'revenue' => fake()->randomFloat(2, 10000, 100000),
            'bounce_rate' => fake()->randomFloat(2, 10, 40),
        ]);
    }

    public function withLowPerformance(): static
    {
        return $this->state(fn(array $attributes) => [
            'views' => fake()->numberBetween(0, 100),
            'clicks' => fake()->numberBetween(0, 10),
            'conversions' => fake()->numberBetween(0, 2),
            'revenue' => fake()->randomFloat(2, 0, 500),
            'bounce_rate' => fake()->randomFloat(2, 70, 95),
        ]);
    }

    public function forDateRange(\DateTime $startDate, \DateTime $endDate): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => fake()->dateTimeBetween($startDate, $endDate),
        ]);
    }
}
