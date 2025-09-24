<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantStockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantStockHistory>
 */
class VariantStockHistoryFactory extends Factory
{
    protected $model = VariantStockHistory::class;

    public function definition(): array
    {
        $changeTypes = ['increase', 'decrease', 'adjustment', 'reserve', 'unreserve'];
        $changeReasons = ['sale', 'return', 'adjustment', 'reserve', 'unreserve', 'damage', 'theft', 'expired', 'manual'];
        $referenceTypes = ['order', 'reservation'];

        $changeType = $this->faker->randomElement($changeTypes);
        $oldQuantity = $this->faker->numberBetween(0, 100);
        $quantityChange = match ($changeType) {
            'increase' => $this->faker->numberBetween(1, 20),
            'decrease' => -$this->faker->numberBetween(1, min(10, $oldQuantity)),
            'adjustment' => $this->faker->numberBetween(-5, 10),
            'reserve' => -$this->faker->numberBetween(1, min(5, $oldQuantity)),
            'unreserve' => $this->faker->numberBetween(1, 10),
            default => 0,
        };
        $newQuantity = max(0, $oldQuantity + $quantityChange);

        return [
            'variant_id' => ProductVariant::factory(),
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'quantity_change' => $quantityChange,
            'change_type' => $changeType,
            'change_reason' => $this->faker->randomElement($changeReasons),
            'changed_by' => User::factory(),
            'reference_type' => $changeType === 'increase' || $changeType === 'decrease' ? $this->faker->randomElement($referenceTypes) : null,
            'reference_id' => $changeType === 'increase' || $changeType === 'decrease' ? $this->faker->numberBetween(1, 100) : null,
        ];
    }

    public function increase(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_type' => 'increase',
            'quantity_change' => $this->faker->numberBetween(1, 20),
        ]);
    }

    public function decrease(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_type' => 'decrease',
            'quantity_change' => -$this->faker->numberBetween(1, 10),
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_type' => 'adjustment',
            'quantity_change' => $this->faker->numberBetween(-5, 10),
        ]);
    }

    public function reserve(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_type' => 'reserve',
            'quantity_change' => -$this->faker->numberBetween(1, 5),
        ]);
    }

    public function unreserve(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_type' => 'unreserve',
            'quantity_change' => $this->faker->numberBetween(1, 10),
        ]);
    }
}
