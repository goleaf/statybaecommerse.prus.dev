<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
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
            // Link the inventory record to both the product and its warehouse.
            'product_id'   => Product::factory(),
            'warehouse_id' => Location::factory(),
            // Provide a predictable SKU-like identifier to exercise ordering logic.
            'sku' => $this->faker->unique()->bothify('INV-####'),
            // Seed an integer quantity via the modern qty attribute.
            'qty' => $this->faker->numberBetween(0, 1000),
            // Bundle secondary stock metrics inside the metadata payload.
            'meta' => [
                'reserved'   => $this->faker->numberBetween(0, 100),
                'incoming'   => $this->faker->numberBetween(0, 200),
                'threshold'  => $this->faker->numberBetween(5, 50),
                'is_tracked' => $this->faker->boolean(80), // 80% chance of being tracked.
            ],
        ];
    }

    public function tracked(): static
    {
        return $this->state(fn (array $attributes) => [
            // Ensure the metadata reflects a tracked inventory row.
            'meta' => $this->mergeMeta($attributes, ['is_tracked' => true]),
        ]);
    }

    public function notTracked(): static
    {
        return $this->state(fn (array $attributes) => [
            // Toggle the tracked flag off for feature specific assertions.
            'meta' => $this->mergeMeta($attributes, ['is_tracked' => false]),
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            // Provide a small quantity and an elevated threshold to simulate low stock.
            'qty'  => $this->faker->numberBetween(1, 5),
            'meta' => $this->mergeMeta($attributes, ['threshold' => $this->faker->numberBetween(10, 20)]),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            // Force quantity and reserved counters to zero when stock is depleted.
            'qty'  => 0,
            'meta' => $this->mergeMeta($attributes, ['reserved' => 0]),
        ]);
    }

    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            // Create a healthy quantity that easily exceeds the reorder threshold.
            'qty'  => $this->faker->numberBetween(50, 500),
            'meta' => $this->mergeMeta($attributes, ['threshold' => $this->faker->numberBetween(5, 20)]),
        ]);
    }

    public function needsReorder(): static
    {
        return $this->state(fn (array $attributes) => [
            // Keep the track flag enabled for realistic reorder checks.
            'qty'  => $this->faker->numberBetween(1, 5),
            'meta' => $this->mergeMeta($attributes, [
                'threshold'  => $this->faker->numberBetween(10, 20),
                'is_tracked' => true,
            ]),
        ]);
    }

    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            // Model a replenished warehouse with generous incoming stock.
            'qty'  => $this->faker->numberBetween(200, 1000),
            'meta' => $this->mergeMeta($attributes, [
                'reserved'   => $this->faker->numberBetween(0, 50),
                'incoming'   => $this->faker->numberBetween(0, 100),
                'threshold'  => $this->faker->numberBetween(20, 50),
                'is_tracked' => true,
            ]),
        ]);
    }

    public function reserved(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['qty'] ?? $this->faker->numberBetween(20, 100);
            $reserved = $this->faker->numberBetween(5, min(15, $quantity));

            return [
                'qty'  => $quantity,
                'meta' => $this->mergeMeta($attributes, [
                    'reserved'   => $reserved,
                    'is_tracked' => true,
                ]),
            ];
        });
    }

    public function withIncoming(): static
    {
        return $this->state(fn (array $attributes) => [
            // Mirror a delivery in transit for reporting scenarios.
            'meta' => $this->mergeMeta($attributes, [
                'incoming'   => $this->faker->numberBetween(50, 200),
                'is_tracked' => true,
            ]),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            // Active inventory should be tracked with a sensible quantity range.
            'qty'  => $this->faker->numberBetween(10, 100),
            'meta' => $this->mergeMeta($attributes, ['is_tracked' => true]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            // Inactive inventory carries no stock and disables tracking.
            'qty'  => 0,
            'meta' => $this->mergeMeta($attributes, [
                'reserved'   => 0,
                'incoming'   => 0,
                'is_tracked' => false,
            ]),
        ]);
    }

    /**
     * Merge helper ensuring metadata overrides retain existing keys where possible.
     *
     * @param  array<string, mixed> $attributes
     * @param  array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function mergeMeta(array $attributes, array $overrides): array
    {
        $meta = $attributes['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        return array_merge($meta, $overrides);
    }
}
