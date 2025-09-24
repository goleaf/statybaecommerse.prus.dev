<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationTemplate>
 */
final class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $type = $this->faker->randomElement(['email', 'sms', 'push', 'in_app']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $type,
            'event' => $this->faker->randomElement([
                'user_registered',
                'order_created',
                'order_shipped',
                'payment_received',
                'product_reviewed',
                'newsletter_subscribed',
                'password_reset',
                'account_verified',
            ]),
            'subject' => [
                'en' => $this->faker->sentence(),
                'lt' => $this->faker->sentence(),
            ],
            'content' => [
                'en' => $this->faker->paragraphs(3, true),
                'lt' => $this->faker->paragraphs(3, true),
            ],
            'variables' => $this->faker->optional(0.7)->randomElements([
                'name', 'email', 'order_number', 'product_name', 'amount', 'date', 'url', 'company_name',
            ], 3),
            'is_active' => $this->faker->boolean(80),
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

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
        ]);
    }

    public function sms(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sms',
        ]);
    }

    public function push(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'push',
        ]);
    }

    public function inApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'in_app',
        ]);
    }

    public function forEvent(string $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => $event,
        ]);
    }

    public function withVariables(array $variables): static
    {
        return $this->state(fn (array $attributes) => [
            'variables' => $variables,
        ]);
    }

    public function withMultilingualContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => [
                'en' => $this->faker->sentence(),
                'lt' => $this->faker->sentence(),
                'de' => $this->faker->sentence(),
                'fr' => $this->faker->sentence(),
                'es' => $this->faker->sentence(),
            ],
            'content' => [
                'en' => $this->faker->paragraphs(3, true),
                'lt' => $this->faker->paragraphs(3, true),
                'de' => $this->faker->paragraphs(3, true),
                'fr' => $this->faker->paragraphs(3, true),
                'es' => $this->faker->paragraphs(3, true),
            ],
        ]);
    }
}
