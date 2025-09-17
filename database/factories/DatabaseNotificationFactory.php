<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Illuminate\Notifications\DatabaseNotification>
 */
class DatabaseNotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'type' => $this->faker->randomElement([
                'App\Notifications\OrderNotification',
                'App\Notifications\ProductNotification',
                'App\Notifications\SystemNotification',
                'App\Notifications\UserNotification',
                'App\Notifications\PaymentNotification',
                'App\Notifications\ShippingNotification',
                'App\Notifications\ReviewNotification',
                'App\Notifications\PromotionNotification',
                'App\Notifications\NewsletterNotification',
                'App\Notifications\SupportNotification',
            ]),
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => [
                'title' => $this->faker->sentence(3),
                'message' => $this->faker->paragraph(2),
                'type' => $this->faker->randomElement([
                    'order', 'product', 'user', 'system', 'payment',
                    'shipping', 'review', 'promotion', 'newsletter', 'support',
                ]),
                'action_url' => $this->faker->url(),
                'icon' => $this->faker->randomElement([
                    'heroicon-o-bell',
                    'heroicon-o-shopping-bag',
                    'heroicon-o-cube',
                    'heroicon-o-user',
                    'heroicon-o-cog-6-tooth',
                ]),
            ],
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Create an unread notification.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Create a read notification.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create an order notification.
     */
    public function order(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'App\Notifications\OrderNotification',
            'data' => array_merge($attributes['data'] ?? [], [
                'type' => 'order',
                'title' => $this->faker->randomElement([
                    'Order Confirmed',
                    'Order Shipped',
                    'Order Delivered',
                    'Order Cancelled',
                    'Payment Received',
                ]),
                'message' => $this->faker->randomElement([
                    'Your order has been confirmed and is being processed.',
                    'Your order has been shipped and is on its way.',
                    'Your order has been delivered successfully.',
                    'Your order has been cancelled as requested.',
                    'Payment for your order has been received.',
                ]),
            ]),
        ]);
    }

    /**
     * Create a product notification.
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'App\Notifications\ProductNotification',
            'data' => array_merge($attributes['data'] ?? [], [
                'type' => 'product',
                'title' => $this->faker->randomElement([
                    'New Product Available',
                    'Product Back in Stock',
                    'Product Price Drop',
                    'Product Review Request',
                ]),
                'message' => $this->faker->randomElement([
                    'Check out our new product that might interest you.',
                    'A product you were interested in is back in stock.',
                    'The price of a product you viewed has dropped.',
                    'Please review your recent purchase.',
                ]),
            ]),
        ]);
    }

    /**
     * Create a system notification.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'App\Notifications\SystemNotification',
            'data' => array_merge($attributes['data'] ?? [], [
                'type' => 'system',
                'title' => $this->faker->randomElement([
                    'System Maintenance',
                    'Security Update',
                    'Feature Update',
                    'Service Announcement',
                ]),
                'message' => $this->faker->randomElement([
                    'Scheduled maintenance will occur tonight from 2-4 AM.',
                    'A security update has been applied to your account.',
                    'New features have been added to the platform.',
                    'Important service announcement for all users.',
                ]),
            ]),
        ]);
    }
}
