<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantPriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantPriceHistory>
 */
final class VariantPriceHistoryFactory extends Factory
{
    protected $model = VariantPriceHistory::class;

    public function definition(): array
    {
        $oldPrice = $this->faker->randomFloat(4, 1, 100);
        $newPrice = $this->faker->randomFloat(4, 1, 100);

        return [
            'variant_id' => ProductVariant::factory(),
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'price_type' => $this->faker->randomElement(['regular', 'sale', 'wholesale', 'bulk']),
            'change_reason' => $this->faker->randomElement([
                'manual',
                'automatic',
                'promotion',
                'cost_change',
                'market_adjustment',
                'seasonal',
            ]),
            'changed_by' => User::factory(),
            'effective_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'effective_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function increase(): static
    {
        return $this->state(function (array $attributes) {
            $oldPrice = $attributes['old_price'] ?? $this->faker->randomFloat(4, 1, 50);
            $newPrice = $oldPrice + $this->faker->randomFloat(4, 1, 20);

            return [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ];
        });
    }

    public function decrease(): static
    {
        return $this->state(function (array $attributes) {
            $oldPrice = $attributes['old_price'] ?? $this->faker->randomFloat(4, 20, 100);
            $newPrice = $oldPrice - $this->faker->randomFloat(4, 1, 15);

            return [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ];
        });
    }

    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'regular',
        ]);
    }

    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'sale',
        ]);
    }

    public function wholesale(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'wholesale',
        ]);
    }

    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_type' => 'bulk',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_reason' => 'manual',
        ]);
    }

    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_reason' => 'automatic',
        ]);
    }

    public function promotion(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_reason' => 'promotion',
        ]);
    }
}
