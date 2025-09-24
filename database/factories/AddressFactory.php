<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
final class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['shipping', 'billing', 'home', 'work', 'other']),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->optional(0.3)->company(),
            'company_vat' => $this->faker->optional(0.2)->numerify('LT########'),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->optional(0.3)->secondaryAddress(),
            'apartment' => $this->faker->optional(0.2)->numerify('Apt ###'),
            'floor' => $this->faker->optional(0.1)->numerify('#th Floor'),
            'building' => $this->faker->optional(0.1)->buildingNumber(),
            'city' => $this->faker->city(),
            'state' => $this->faker->optional(0.7)->state(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->countryCode(),
            'phone' => $this->faker->optional(0.8)->phoneNumber(),
            'email' => $this->faker->optional(0.6)->safeEmail(),
            'is_default' => $this->faker->boolean(20),
            'is_billing' => $this->faker->boolean(30),
            'is_shipping' => $this->faker->boolean(30),
            'is_active' => $this->faker->boolean(95),
            'notes' => $this->faker->optional(0.2)->sentence(),
            'instructions' => $this->faker->optional(0.1)->sentence(),
            'landmark' => $this->faker->optional(0.1)->sentence(),
        ];
    }

    /**
     * Indicate that the address is a shipping address.
     */
    public function shipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shipping',
        ]);
    }

    /**
     * Indicate that the address is a billing address.
     */
    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'billing',
        ]);
    }

    /**
     * Indicate that the address is the default address.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the address is a home address.
     */
    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'home',
        ]);
    }

    /**
     * Indicate that the address is a work address.
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'work',
        ]);
    }

    /**
     * Indicate that the address is an other type address.
     */
    public function other(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'other',
        ]);
    }

    /**
     * Indicate that the address is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the address is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the address has company information.
     */
    public function withCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_name' => fake()->company(),
            'company_vat' => fake()->numerify('LT########'),
        ]);
    }

    /**
     * Indicate that the address has additional information.
     */
    public function withAdditionalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'apartment' => fake()->numerify('Apt ###'),
            'floor' => fake()->numerify('#th Floor'),
            'building' => fake()->buildingNumber(),
            'landmark' => fake()->sentence(),
            'instructions' => fake()->sentence(),
        ]);
    }
}
