<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DiscountConditionFactory extends Factory
{
    protected $model = DiscountCondition::class;

    public function definition(): array
    {
        $types = [
            'product', 'category', 'brand', 'collection', 'attribute_value',
            'cart_total', 'item_qty', 'zone', 'channel', 'currency',
            'customer_group', 'user', 'partner_tier', 'first_order', 'day_time', 'custom_script',
        ];
        $operators = [
            'equals_to', 'not_equals_to', 'less_than', 'greater_than', 'starts_with', 'ends_with', 'contains', 'not_contains',
        ];

        return [
            'discount_id' => Discount::factory(),
            'type' => $this->faker->randomElement($types),
            'operator' => $this->faker->randomElement($operators),
            'value' => [$this->faker->word()],
            'position' => 0,
        ];
    }
}

