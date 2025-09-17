<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Legal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Legal>
 */
class LegalFactory extends Factory
{
    protected $model = Legal::class;

    public function definition(): array
    {
        $types = array_keys(\App\Models\Legal::getTypes());
        $type = fake()->randomElement($types);
        $key = fake()->unique()->slug(2);

        return [
            'key' => $key,
            'type' => $type,
            'is_enabled' => fake()->boolean(80), // 80% chance of being enabled
            'is_required' => fake()->boolean(20), // 20% chance of being required
            'sort_order' => fake()->numberBetween(0, 100),
            'meta_data' => [
                'version' => fake()->randomFloat(1, 1.0, 5.0),
                'last_reviewed' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'review_frequency' => fake()->randomElement(['monthly', 'quarterly', 'annually']),
            ],
            'published_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-1 year', 'now') : null,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'privacy-policy',
            'type' => 'privacy_policy',
            'is_required' => true,
        ]);
    }

    public function termsOfUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'terms-of-use',
            'type' => 'terms_of_use',
            'is_required' => true,
        ]);
    }

    public function refundPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'refund-policy',
            'type' => 'refund_policy',
        ]);
    }

    public function shippingPolicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'shipping-policy',
            'type' => 'shipping_policy',
        ]);
    }
}
