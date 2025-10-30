<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

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

        // Generate a unique, deterministic email without querying the database to prevent SQLite "database is locked" errors during concurrent factory usage.
        $email = sprintf(
            '%s.%s.%s@example.test',
            Str::slug($firstName),
            Str::slug($lastName),
            Str::lower(Str::random(6))
        );

        $state = [
            'name'              => $firstName . ' ' . $lastName,
            // Store decomposed name parts so seeders and UI components can reuse
            // deterministic values without re-parsing the combined name.
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'email'             => $email,
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'preferred_locale'  => fake()->randomElement(['en', 'lt']),
            'is_admin'          => false,
            'remember_token'    => Str::random(10),
        ];

        // Some test runs (e.g. partial migrations, in-memory DBs) may not yet
        // include recently added columns. Only set optional flags when present.
        if ($this->tableExists('users') && $this->tableHasColumn('users', 'is_active')) {
            $state['is_active'] = true;
        }

        return $state;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin with a verified email and active status.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin'          => true,
            'email_verified_at' => now(),
            'is_active'         => true,
            'password'          => static::$password ??= Hash::make('password'),
        ]);
    }

    public function shippingAddress(): static
    {
        return $this->hasAddresses(1, fn (): array => [
            'type'           => AddressType::SHIPPING,
            'is_default'     => true,
            'is_shipping'    => true,
            'country_code'   => 'LT',
            'city'           => 'Vilnius',
            'address_line_1' => 'Gedimino pr. 1',
            'postal_code'    => '01103',
            'phone'          => '+370' . fake()->numberBetween(60000000, 69999999),
        ]);
    }

    public function billingAddress(): static
    {
        return $this->hasAddresses(1, fn (): array => [
            'type'           => AddressType::BILLING,
            // Ensure billing addresses do not unintentionally become the
            // customer's default entry because factories rely on null defaults.
            'is_default'     => false,
            'is_billing'     => true,
            'country_code'   => 'LT',
            'city'           => 'Vilnius',
            'address_line_1' => 'Konstitucijos pr. 7',
            'postal_code'    => '09308',
        ]);
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
