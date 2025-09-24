<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
final class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $types = ['in', 'out'];
        $reasons = ['sale', 'return', 'adjustment', 'manual_adjustment', 'restock', 'damage', 'theft', 'transfer'];

        return [
            'variant_inventory_id' => VariantInventory::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement($types),
            'reason' => $this->faker->randomElement($reasons),
            'reference' => $this->faker->optional()->regexify('[A-Z]{2}-[0-9]{5}'),
            'notes' => $this->faker->optional()->sentence(),
            'user_id' => $this->faker->optional()->randomElement([User::factory()]),
            'moved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in',
        ]);
    }

    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'out',
        ]);
    }

    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'out',
            'reason' => 'sale',
        ]);
    }

    public function restock(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in',
            'reason' => 'restock',
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'adjustment',
        ]);
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'moved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'moved_at' => $this->faker->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }
}
