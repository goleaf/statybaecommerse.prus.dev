<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => round($this->faker->randomFloat(2, 5, 50), 2),
            'is_active' => true,
            'is_enabled' => true,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
            'usage_limit' => $this->faker->numberBetween(10, 100),
            'usage_count' => 0,
            'minimum_amount' => round($this->faker->randomFloat(2, 0, 100), 2),
            'zone_id' => Zone::factory(),
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => round($this->faker->randomFloat(2, 5, 30), 2),
        ]);
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => round($this->faker->randomFloat(2, 5, 100), 2),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);
    }
}
