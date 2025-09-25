<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscountRedemption>
 */
final class DiscountRedemptionFactory extends Factory
{
    protected $model = DiscountRedemption::class;

    public function definition(): array
    {
        return [
            'discount_id' => Discount::factory(),
            'code_id' => DiscountCode::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'amount_saved' => $this->faker->randomFloat(2, 5, 100),
            'currency_code' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'redeemed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement(['pending', 'redeemed', 'expired', 'cancelled']),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function redeemed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'redeemed',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'redeemed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_saved' => $this->faker->randomFloat(2, 50, 500),
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_code' => 'EUR',
        ]);
    }

    public function forDiscount(?Discount $discount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_id' => $discount?->getKey() ?? Discount::factory(),
        ]);
    }

    public function forCode(?DiscountCode $discountCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'code_id' => $discountCode?->getKey() ?? DiscountCode::factory(),
        ]);
    }
}
