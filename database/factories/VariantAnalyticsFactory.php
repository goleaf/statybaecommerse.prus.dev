<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantAnalytics>
 */
final class VariantAnalyticsFactory extends Factory
{
    protected $model = VariantAnalytics::class;

    public function definition(): array
    {
        $views = fake()->numberBetween(10, 1000);
        $clicks = fake()->numberBetween(1, $views);
        $addToCart = fake()->numberBetween(1, $clicks);
        $purchases = fake()->numberBetween(1, $addToCart);

        return [
            'variant_id' => ProductVariant::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'views' => $views,
            'clicks' => $clicks,
            'add_to_cart' => $addToCart,
            'purchases' => $purchases,
            'revenue' => fake()->randomFloat(4, 10, 1000),
            'conversion_rate' => fake()->randomFloat(4, 0, 100),
        ];
    }

    public function highPerforming(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(500, 2000),
            'clicks' => fake()->numberBetween(100, 800),
            'add_to_cart' => fake()->numberBetween(50, 400),
            'purchases' => fake()->numberBetween(20, 200),
            'revenue' => fake()->randomFloat(4, 500, 5000),
            'conversion_rate' => fake()->randomFloat(4, 5, 25),
        ]);
    }

    public function lowPerforming(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(10, 100),
            'clicks' => fake()->numberBetween(1, 20),
            'add_to_cart' => fake()->numberBetween(1, 10),
            'purchases' => fake()->numberBetween(0, 5),
            'revenue' => fake()->randomFloat(4, 0, 50),
            'conversion_rate' => fake()->randomFloat(4, 0, 2),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function historical(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    public function withVariant(ProductVariant $variant): static
    {
        return $this->state(fn (array $attributes) => [
            'variant_id' => $variant->id,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}
