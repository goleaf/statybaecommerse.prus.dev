<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Partner;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantInventory>
 */
class VariantInventoryFactory extends Factory
{
    protected $model = VariantInventory::class;

    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'warehouse_code' => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
            'stock' => $this->faker->numberBetween(0, 1000),
            'reserved' => $this->faker->numberBetween(0, 100),
            'available' => $this->faker->numberBetween(0, 900),
            'reorder_point' => $this->faker->numberBetween(1, 20),
            'reorder_quantity' => $this->faker->numberBetween(50, 200),
            'max_stock_level' => $this->faker->numberBetween(500, 2000),
            'cost_per_unit' => $this->faker->randomFloat(2, 1, 100),
            'supplier_id' => Partner::factory(),
            'batch_number' => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'discontinued']),
            'notes' => $this->faker->optional()->sentence(),
            'last_restocked_at' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'last_sold_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 10),
            'reorder_point' => $this->faker->numberBetween(15, 25),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'reserved' => 0,
        ]);
    }

    public function needsReorder(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 5),
            'reorder_point' => $this->faker->numberBetween(10, 20),
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function tracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function notTracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function discontinued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'discontinued',
        ]);
    }
}
