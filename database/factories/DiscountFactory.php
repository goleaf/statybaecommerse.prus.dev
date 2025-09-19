<?php declare(strict_types=1);

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
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['percentage', 'fixed']),
            'value' => fake()->randomFloat(2, 5, 50),
            'is_active' => true,
            'is_enabled' => true,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
            'usage_limit' => fake()->numberBetween(10, 100),
            'usage_count' => 0,
            'minimum_amount' => fake()->randomFloat(2, 0, 100),
            'zone_id' => Zone::factory(),
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'percentage',
            'value' => $this->faker->randomFloat(2, 5, 30),
        ]);
    }

    public function fixed(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'fixed',
            'value' => $this->faker->randomFloat(2, 5, 100),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => true,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);
    }
}
