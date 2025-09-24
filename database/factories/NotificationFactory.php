<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
final class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['info', 'success', 'warning', 'error']);
        $notificationType = $this->faker->randomElement(['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support']);

        return [
            'id' => (string) Str::uuid(),
            'type' => "App\\Notifications\\{$notificationType}Notification",
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => [
                'title' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(),
                'type' => $type,
                'urgent' => $this->faker->boolean(10),
                'tags' => $this->faker->optional(0.3)->randomElements(['important', 'update', 'reminder', 'promotion'], 2),
                'color' => $this->faker->optional(0.2)->randomElement(['blue', 'green', 'yellow', 'red', 'purple']),
                'attachment' => $this->faker->optional(0.1)->url(),
            ],
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'urgent' => true,
            ]),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'type' => $type,
            ]),
        ]);
    }

    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'tags' => $tags,
            ]),
        ]);
    }

    public function withAttachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'attachment' => $this->faker->url(),
            ]),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }
}
