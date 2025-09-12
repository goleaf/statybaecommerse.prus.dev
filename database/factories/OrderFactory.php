<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 1000);
        $taxAmount = $subtotal * 0.21; // 21% VAT
        $shippingAmount = $this->faker->randomFloat(2, 0, 20);
        $discountAmount = $this->faker->randomFloat(2, 0, $subtotal * 0.1); // Max 10% discount
        $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        return [
            'number' => 'ORD-'.strtoupper($this->faker->unique()->bothify('######')),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'confirmed', 'shipped', 'delivered', 'completed', 'cancelled']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => 'EUR',
            'billing_address' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->country(),
            ],
            'shipping_address' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->country(),
            ],
            'notes' => $this->faker->optional(0.3)->sentence(),
            'shipped_at' => $this->faker->optional(0.4)->dateTimeBetween('-30 days', 'now'),
            'delivered_at' => $this->faker->optional(0.2)->dateTimeBetween('-30 days', 'now'),
            'channel_id' => $this->faker->optional(0.7)->randomElement(Channel::pluck('id')->toArray()),
            'zone_id' => $this->faker->optional(0.8)->randomElement(Zone::pluck('id')->toArray()),
            'partner_id' => $this->faker->optional(0.3)->randomElement(Partner::pluck('id')->toArray()),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded', 'partially_refunded']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'paypal', 'cash_on_delivery']),
            'payment_reference' => $this->faker->optional(0.6)->bothify('PAY-########'),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'payment_status' => 'paid',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'payment_status' => 'paid',
            'shipped_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'shipped_at' => $this->faker->dateTimeBetween('-14 days', '-7 days'),
            'delivered_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'shipped_at' => $this->faker->dateTimeBetween('-30 days', '-14 days'),
            'delivered_at' => $this->faker->dateTimeBetween('-14 days', '-7 days'),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'pending',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the order has failed payment.
     */
    public function paymentFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order has been refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'refunded',
        ]);
    }

    /**
     * Indicate that the order has high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 500, 5000),
            'tax_amount' => $this->faker->randomFloat(2, 100, 1000),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 50),
            'discount_amount' => $this->faker->randomFloat(2, 0, 200),
            'total' => $this->faker->randomFloat(2, 600, 6000),
        ]);
    }

    /**
     * Indicate that the order has low value.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 5, 50),
            'tax_amount' => $this->faker->randomFloat(2, 1, 10),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 5),
            'discount_amount' => 0,
            'total' => $this->faker->randomFloat(2, 6, 60),
        ]);
    }

    /**
     * Indicate that the order is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the order is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }
}
