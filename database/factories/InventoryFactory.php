<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
final class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'location_id' => Location::factory(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'reserved' => $this->faker->numberBetween(0, 100),
            'incoming' => $this->faker->numberBetween(0, 200),
            'threshold' => $this->faker->numberBetween(5, 50),
            'is_tracked' => $this->faker->boolean(80), // 80% chance of being tracked
        ];
    }

    public function tracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_tracked' => true,
        ]);
    }

    public function notTracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_tracked' => false,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 5),
            'threshold' => $this->faker->numberBetween(10, 20),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'reserved' => 0,
        ]);
    }

    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(50, 500),
            'threshold' => $this->faker->numberBetween(5, 20),
        ]);
    }
}