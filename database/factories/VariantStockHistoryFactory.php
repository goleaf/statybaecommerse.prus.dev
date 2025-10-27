<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantStockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\VariantStockHistory>
 */
class VariantStockHistoryFactory extends Factory
{
    protected $model = VariantStockHistory::class;

    public function definition(): array
    {
        $changeReasons = ['sale', 'return', 'adjustment', 'reserve', 'unreserve', 'damage', 'theft', 'expired', 'manual'];
        $referenceTypes = ['order', 'reservation'];

        return [
            'variant_id'      => ProductVariant::factory(),
            'old_quantity'    => $this->faker->numberBetween(0, 100),
            'change_type'     => $this->faker->randomElement(['increase', 'decrease', 'adjustment', 'reserve', 'unreserve']),
            'quantity_change' => function (array $attributes): int {
                return $this->randomQuantityChange(
                    $attributes['change_type'] ?? 'adjustment',
                    (int) ($attributes['old_quantity'] ?? 0),
                );
            },
            'new_quantity'    => function (array $attributes): int {
                $oldQuantity = (int) ($attributes['old_quantity'] ?? 0);
                $quantityChange = (int) ($attributes['quantity_change'] ?? 0);

                return max(0, $oldQuantity + $quantityChange);
            },
            'change_reason'   => $this->faker->randomElement($changeReasons),
            'changed_by'      => User::factory(),
            'reference_type'  => function (array $attributes) use ($referenceTypes): ?string {
                return in_array($attributes['change_type'] ?? null, ['increase', 'decrease'], true)
                    ? $this->faker->randomElement($referenceTypes)
                    : null;
            },
            'reference_id'    => function (array $attributes): ?int {
                return in_array($attributes['change_type'] ?? null, ['increase', 'decrease'], true)
                    ? $this->faker->numberBetween(1, 100)
                    : null;
            },
        ];
    }

    /**
     * State for setting both old_quantity and new_quantity explicitly.
     * This will ensure quantity_change is properly calculated.
     */
    public function withQuantities(int $oldQuantity, int $newQuantity): static
    {
        return $this->state(fn (array $attributes) => [
            ...$attributes,
            'old_quantity'    => $oldQuantity,
            'new_quantity'    => $newQuantity,
            'quantity_change' => $newQuantity - $oldQuantity,
        ]);
    }

    public function increase(): static
    {
        return $this->state(fn (array $attributes) => $this->applyChangeTypeState('increase', $attributes));
    }

    public function decrease(): static
    {
        return $this->state(fn (array $attributes) => $this->applyChangeTypeState('decrease', $attributes));
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => $this->applyChangeTypeState('adjustment', $attributes));
    }

    public function reserve(): static
    {
        return $this->state(fn (array $attributes) => $this->applyChangeTypeState('reserve', $attributes));
    }

    public function unreserve(): static
    {
        return $this->state(fn (array $attributes) => $this->applyChangeTypeState('unreserve', $attributes));
    }

    private function applyChangeTypeState(string $changeType, array $attributes): array
    {
        $oldQuantity = isset($attributes['old_quantity'])
            ? (int) $attributes['old_quantity']
            : $this->faker->numberBetween(0, 100);

        $quantityAttribute = $attributes['quantity_change'] ?? null;
        $quantityChange = $quantityAttribute instanceof \Closure
            ? null
            : $quantityAttribute;

        $quantityChange ??= $this->randomQuantityChange($changeType, $oldQuantity);

        $newQuantityAttribute = $attributes['new_quantity'] ?? null;
        $newQuantity = $newQuantityAttribute instanceof \Closure
            ? null
            : $newQuantityAttribute;

        $newQuantity ??= $oldQuantity + $quantityChange;

        return [
            ...$attributes,
            'change_type'     => $changeType,
            'old_quantity'    => $oldQuantity,
            'quantity_change' => $quantityChange,
            'new_quantity'    => max(0, $newQuantity),
        ];
    }

    private function randomQuantityChange(string $changeType, int $oldQuantity): int
    {
        $availableStock = max(1, $oldQuantity);

        return match ($changeType) {
            'increase'   => $this->faker->numberBetween(1, 20),
            'decrease'   => -$this->faker->numberBetween(1, min(10, $availableStock)),
            'reserve'    => -$this->faker->numberBetween(1, min(5, $availableStock)),
            'unreserve'  => $this->faker->numberBetween(1, 10),
            default      => $this->faker->numberBetween(-5, 10),
        };
    }
}
