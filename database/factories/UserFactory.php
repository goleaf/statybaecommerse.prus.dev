<?php

namespace Database\Factories;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        // Generate unique email
        $baseEmail = fake()->safeEmail();
        $email = $baseEmail;
        $counter = 1;
        while (\App\Models\User::where('email', $email)->exists()) {
            $emailParts = explode('@', $baseEmail);
            $email = $emailParts[0] . $counter . '@' . $emailParts[1];
            $counter++;
        }

        return [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'preferred_locale' => fake()->randomElement(['en', 'lt']),
            'is_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin with a verified email and active status.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_admin' => true,
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => static::$password ??= Hash::make('password'),
        ]);
    }

    public function shippingAddress(): static
    {
        return $this->hasAddresses(1, fn(): array => [
            'type' => AddressType::SHIPPING,
            'is_default' => true,
            'is_shipping' => true,
            'country_code' => 'LT',
            'city' => 'Vilnius',
            'address_line_1' => 'Gedimino pr. 1',
            'postal_code' => '01103',
            'phone' => '+370' . fake()->numberBetween(60000000, 69999999),
        ]);
    }

    public function billingAddress(): static
    {
        return $this->hasAddresses(1, fn(): array => [
            'type' => AddressType::BILLING,
            'is_billing' => true,
            'country_code' => 'LT',
            'city' => 'Vilnius',
            'address_line_1' => 'Konstitucijos pr. 7',
            'postal_code' => '09308',
        ]);
    }
}
