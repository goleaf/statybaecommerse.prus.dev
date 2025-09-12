<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscountCondition>
 */
final class DiscountConditionFactory extends Factory
{
    protected $model = DiscountCondition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(DiscountCondition::getTypes());
        $type = $this->faker->randomElement($types);
        $operators = array_keys(DiscountCondition::getOperatorsForType($type));
        $operator = $this->faker->randomElement($operators);

        return [
            'discount_id' => Discount::factory(),
            'type' => $type,
            'operator' => $operator,
            'value' => $this->generateValueForType($type),
            'position' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'priority' => $this->faker->numberBetween(0, 10),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'test' => 'data',
                'category' => 'electronics',
                'brand' => 'test_brand',
            ]),
        ];
    }

    /**
     * Generate appropriate value based on condition type
     */
    private function generateValueForType(string $type): mixed
    {
        return match ($type) {
            'cart_total', 'item_qty', 'priority' => $this->faker->numberBetween(1, 1000),
            'product', 'category', 'brand', 'collection', 'attribute_value', 'zone', 'channel', 'currency', 'customer_group', 'user', 'partner_tier' => $this->faker->word(),
            'first_order', 'day_time' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 2),
            'custom_script' => $this->faker->regexify('[A-Za-z0-9]{10}'),
            default => $this->faker->word(),
        };
    }

    /**
     * Create an active condition
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive condition
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a high priority condition
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(6, 10),
        ]);
    }

    /**
     * Create a low priority condition
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * Create a cart total condition
     */
    public function cartTotal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cart_total',
            'operator' => $this->faker->randomElement(['greater_than', 'less_than', 'equals_to']),
            'value' => $this->faker->numberBetween(10, 500),
        ]);
    }

    /**
     * Create a product condition
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'product',
            'operator' => $this->faker->randomElement(['equals_to', 'contains', 'starts_with']),
            'value' => $this->faker->word(),
        ]);
    }

    /**
     * Create a category condition
     */
    public function category(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'category',
            'operator' => $this->faker->randomElement(['equals_to', 'contains', 'in_array']),
            'value' => $this->faker->randomElements(['electronics', 'clothing', 'books', 'home'], 2),
        ]);
    }
}