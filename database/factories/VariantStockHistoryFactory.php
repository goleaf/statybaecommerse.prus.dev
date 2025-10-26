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
            'increase'   => $this->faker->numberBetween(1, 20),
            'decrease'   => -$this->faker->numberBetween(1, min(10, max(1, $oldQuantity))),
            'adjustment' => $this->faker->numberBetween(-5, 10),
            'reserve'    => -$this->faker->numberBetween(1, min(5, max(1, $oldQuantity))),
            'unreserve'  => $this->faker->numberBetween(1, 10),
            default      => 0,
        };
        $newQuantity = max(0, $oldQuantity + $quantityChange);

        return [
            'variant_id'      => ProductVariant::factory(),
            'old_quantity'    => $oldQuantity,
            'new_quantity'    => $newQuantity,
            'quantity_change' => $quantityChange,  // Use calculated change from match expression
            'change_type'     => $changeType,
            'change_reason'   => $this->faker->randomElement($changeReasons),
            'changed_by'      => User::factory(),
            'reference_type'  => $changeType === 'increase' || $changeType === 'decrease' ? $this->faker->randomElement($referenceTypes) : null,
            'reference_id'    => $changeType === 'increase' || $changeType === 'decrease' ? $this->faker->numberBetween(1, 100) : null,
        ];
    }

    /**
     * State for setting both old_quantity and new_quantity explicitly.
     * This will ensure quantity_change is properly calculated.
     */
    public function withQuantities(int $oldQuantity, int $newQuantity): static
    {
        return $this->state(fn (array $attributes) => [
            'old_quantity'    => $oldQuantity,
            'new_quantity'    => $newQuantity,
            'quantity_change' => $newQuantity - $oldQuantity,
        ]);
    }

    public function increase(): static
    {
        return $this->state(function (array $attributes) {
            $quantityChange = $this->faker->numberBetween(1, 20);
            $oldQuantity = $attributes['old_quantity'] ?? $this->faker->numberBetween(0, 100);
            $newQuantity = $oldQuantity + $quantityChange;

            return [
                'change_type'     => 'increase',
                'old_quantity'    => $oldQuantity,
                'new_quantity'    => $newQuantity,
                'quantity_change' => $quantityChange,
            ];
        });
    }

    public function decrease(): static
    {
        return $this->state(function (array $attributes) {
            $oldQuantity = $attributes['old_quantity'] ?? $this->faker->numberBetween(10, 100);
            $quantityChange = -$this->faker->numberBetween(1, min(10, $oldQuantity));
            $newQuantity = max(0, $oldQuantity + $quantityChange);

            return [
                'change_type'     => 'decrease',
                'old_quantity'    => $oldQuantity,
                'new_quantity'    => $newQuantity,
                'quantity_change' => $quantityChange,
            ];
        });
    }

    public function adjustment(): static
    {
        return $this->state(function (array $attributes) {
            $oldQuantity = $attributes['old_quantity'] ?? $this->faker->numberBetween(0, 100);
            $quantityChange = $this->faker->numberBetween(-5, 10);
            $newQuantity = max(0, $oldQuantity + $quantityChange);

            return [
                'change_type'     => 'adjustment',
                'old_quantity'    => $oldQuantity,
                'new_quantity'    => $newQuantity,
                'quantity_change' => $quantityChange,
            ];
        });
    }

    public function reserve(): static
    {
        return $this->state(function (array $attributes) {
            $oldQuantity = $attributes['old_quantity'] ?? $this->faker->numberBetween(10, 100);
            $quantityChange = -$this->faker->numberBetween(1, min(5, $oldQuantity));
            $newQuantity = max(0, $oldQuantity + $quantityChange);

            return [
                'change_type'     => 'reserve',
                'old_quantity'    => $oldQuantity,
                'new_quantity'    => $newQuantity,
                'quantity_change' => $quantityChange,
            ];
        });
    }

    public function unreserve(): static
    {
        return $this->state(function (array $attributes) {
            $quantityChange = $this->faker->numberBetween(1, 10);
            $oldQuantity = $attributes['old_quantity'] ?? $this->faker->numberBetween(0, 100);
            $newQuantity = $oldQuantity + $quantityChange;

            return [
                'change_type'     => 'unreserve',
                'old_quantity'    => $oldQuantity,
                'new_quantity'    => $newQuantity,
                'quantity_change' => $quantityChange,
            ];
        });
    }
}
