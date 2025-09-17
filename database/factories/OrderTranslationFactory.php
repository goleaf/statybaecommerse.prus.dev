<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Translations\OrderTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\OrderTranslation>
 */
class OrderTranslationFactory extends Factory
{
    protected $model = OrderTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'notes' => $this->faker->optional(0.3)->sentence(),
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
        ];
    }

    /**
     * Indicate that the translation is in Lithuanian.
     */
    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'notes' => $this->faker->optional(0.3)->sentence(),
            'billing_address' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'phone' => '+370'.$this->faker->numerify('#######'),
                'address' => $this->faker->streetAddress(),
                'city' => $this->faker->randomElement(['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai', 'Panevėžys']),
                'postal_code' => 'LT-'.$this->faker->numerify('#####'),
                'country' => 'Lietuva',
            ],
            'shipping_address' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'phone' => '+370'.$this->faker->numerify('#######'),
                'address' => $this->faker->streetAddress(),
                'city' => $this->faker->randomElement(['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai', 'Panevėžys']),
                'postal_code' => 'LT-'.$this->faker->numerify('#####'),
                'country' => 'Lietuva',
            ],
        ]);
    }

    /**
     * Indicate that the translation is in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'notes' => $this->faker->optional(0.3)->sentence(),
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
        ]);
    }
}
