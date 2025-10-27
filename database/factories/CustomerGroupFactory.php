<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerGroup>
 */
final class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attributes = [
            'name'                => $this->faker->words(2, true),
            'code'                => strtoupper($this->faker->bothify('??##')),
            'description'         => $this->faker->sentence(),
            'slug'                => $this->faker->slug(),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 50),
            'is_enabled'          => true,
            'is_active'           => true,
            // Provide sensible B2B defaults so credit limits and terms based
            // assertions in tests have data to interact with out of the box.
            'minimum_order_amount' => $this->faker->randomFloat(2, 0, 500),
            'credit_limit'         => $this->faker->randomFloat(2, 0, 10000),
            'payment_terms'        => $this->faker->randomElement(['net_30', 'net_45', 'net_60']),
            'conditions'           => [],
        ];

        // Only include the JSON metadata payload when the backing table exposes the column to
        // avoid SQLite errors in minimal migration scenarios used during isolated unit tests.
        if (Schema::hasColumn('customer_groups', 'metadata')) {
            $attributes['metadata'] = [
                'type'                 => $this->faker->randomElement(['regular', 'vip', 'corporate', 'retail']),
                'has_special_pricing'  => $this->faker->boolean(30),
                'has_volume_discounts' => $this->faker->boolean(40),
                'can_view_prices'      => $this->faker->boolean(80),
                'can_place_orders'     => $this->faker->boolean(90),
                'can_view_catalog'     => $this->faker->boolean(85),
                'can_use_coupons'      => $this->faker->boolean(70),
                'sort_order'           => $this->faker->numberBetween(1, 100),
            ];
        }

        if (is_string($attributes['name'])) {
            $attributes['name'] = [
                'lt' => $attributes['name'],
                'en' => $attributes['name'],
            ];
        }

        if (is_string($attributes['description'])) {
            $attributes['description'] = [
                'lt' => $attributes['description'],
                'en' => $attributes['description'],
            ];
        }

        return $attributes;
    }

    /**
     * Indicate that the customer group is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the customer group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'  => false,
            'is_enabled' => false,
        ]);
    }

    /**
     * Indicate that the customer group is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the customer group has special pricing.
     */
    public function withSpecialPricing(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_special_pricing' => true,
            'discount_percentage' => $this->faker->randomFloat(2, 5, 25),
        ]);
    }

    /**
     * Indicate that the customer group has volume discounts.
     */
    public function withVolumeDiscounts(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_volume_discounts' => true,
        ]);
    }

    /**
     * Indicate that the customer group has a fixed discount.
     */
    public function withFixedDiscount(float $amount = 10.0): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_fixed' => $amount,
        ]);
    }

    /**
     * Indicate that the customer group has a percentage discount.
     */
    public function withPercentageDiscount(float $percentage = 15.0): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $percentage,
        ]);
    }

    /**
     * Indicate specific group type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate VIP group.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'                 => 'vip',
            'has_special_pricing'  => true,
            'discount_percentage'  => $this->faker->randomFloat(2, 15, 30),
            'has_volume_discounts' => true,
        ]);
    }

    /**
     * Indicate wholesale group.
     */
    public function wholesale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'                 => 'wholesale',
            'has_special_pricing'  => true,
            'discount_percentage'  => $this->faker->randomFloat(2, 20, 40),
            'minimum_order_amount' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    /**
     * Indicate corporate group.
     */
    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'                => 'corporate',
            'has_special_pricing' => true,
            'payment_terms'       => 'net_30',
            'credit_limit'        => $this->faker->randomFloat(2, 5000, 50000),
        ]);
    }

    /**
     * Indicate the group cannot view prices.
     */
    public function withoutPriceAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_view_prices' => false,
        ]);
    }

    /**
     * Indicate the group cannot place orders.
     */
    public function withoutOrderAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_place_orders' => false,
        ]);
    }
}
