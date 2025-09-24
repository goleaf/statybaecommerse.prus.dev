<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnumValue>
 */
class EnumValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'navigation_group' => 'Navigation Group',
            'order_status' => 'Order Status',
            'payment_status' => 'Payment Status',
            'shipping_status' => 'Shipping Status',
            'user_role' => 'User Role',
            'product_status' => 'Product Status',
            'campaign_type' => 'Campaign Type',
            'discount_type' => 'Discount Type',
            'notification_type' => 'Notification Type',
            'document_type' => 'Document Type',
            'address_type' => 'Address Type',
            'priority' => 'Priority',
            'status' => 'Status',
        ];

        $type = fake()->randomElement(array_keys($types));
        $key = fake()->unique()->slug(2);
        $value = fake()->words(2, true);

        return [
            'type' => $type,
            'key' => $key,
            'value' => $value,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => fake()->boolean(80),
            'is_default' => fake()->boolean(10),
            'metadata' => [
                'color' => fake()->hexColor(),
                'icon' => fake()->randomElement(['heroicon-o-star', 'heroicon-o-check', 'heroicon-o-x']),
                'category' => fake()->word(),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function navigationGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'navigation_group',
            'key' => fake()->randomElement(['products', 'orders', 'customers', 'marketing', 'reports', 'system']),
            'value' => fake()->words(2, true),
        ]);
    }

    public function orderStatus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'order_status',
            'key' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']),
            'value' => fake()->words(2, true),
        ]);
    }

    public function paymentStatus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment_status',
            'key' => fake()->randomElement(['pending', 'paid', 'failed', 'refunded', 'partially_refunded']),
            'value' => fake()->words(2, true),
        ]);
    }
}
