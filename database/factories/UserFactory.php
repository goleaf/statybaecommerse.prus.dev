<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;
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
            '%info@egisstatyba.lt',
            Str::slug($firstName),
            Str::slug($lastName),
            Str::lower(Str::random(6))
        );

        $phoneNumber = '+3706' . fake()->numberBetween(1000000, 9999999);

        $state = [];

        if ($this->hasUsersColumn('name')) {
            $state['name'] = $firstName . ' ' . $lastName;
        }

        if ($this->hasUsersColumn('email')) {
            $state['email'] = $email;
        }

        if ($this->hasUsersColumn('password')) {
            // Use a strong default so SecurePasswordHandling validates before hashing.
            $state['password'] = static::$password ??= 'Admin123!';
        }

        if ($this->hasUsersColumn('preferred_locale')) {
            $state['preferred_locale'] = fake()->randomElement(['en', 'lt']);
        }

        if ($this->hasUsersColumn('is_admin')) {
            $state['is_admin'] = false;
        }

        if ($this->hasUsersColumn('remember_token')) {
            $state['remember_token'] = Str::random(10);
        }

        if ($this->hasUsersColumn('phone_number')) {
            $state['phone_number'] = $phoneNumber;
        }

        // Store decomposed name parts only when the schema supports them.
        if ($this->hasUsersColumn('first_name')) {
            $state['first_name'] = $firstName;
        }

        if ($this->hasUsersColumn('last_name')) {
            $state['last_name'] = $lastName;
        }

        // Populate legacy phone field if present to keep data consistent.
        if ($this->hasUsersColumn('phone')) {
            $state['phone'] = $phoneNumber;
        }

        // Some test runs (e.g. partial migrations, in-memory DBs) may not yet
        // include recently added columns. Only set optional flags when present.
        if ($this->hasUsersColumn('email_verified_at')) {
            $state['email_verified_at'] = now();
        }

        if ($this->hasUsersColumn('is_active')) {
            $state['is_active'] = true;
        }

        return $state;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        $state = [];

        if ($this->hasUsersColumn('email_verified_at')) {
            $state['email_verified_at'] = null;
        }

        return $this->state(fn (array $attributes) => [
            ...$state,
        ]);
    }

    /**
     * Indicate that the user is an admin with a verified email and active status.
     */
    public function admin(): static
    {
        $state = [];

        if ($this->hasUsersColumn('is_admin')) {
            $state['is_admin'] = true;
        }

        if ($this->hasUsersColumn('password')) {
            // Reuse the strong default so admin fixtures pass SecurePasswordHandling validation.
            $state['password'] = static::$password ??= 'Admin123!';
        }

        if ($this->hasUsersColumn('email_verified_at')) {
            $state['email_verified_at'] = now();
        }

        if ($this->hasUsersColumn('is_active')) {
            $state['is_active'] = true;
        }

        return $this->state(fn (array $attributes) => [
            ...$state,
        ]);
    }

    public function shippingAddress(): static
    {
        $phoneNumber = '+370' . fake()->numberBetween(60000000, 69999999);

        return $this->hasAddresses(1, fn (): array => [
            'type'           => AddressType::SHIPPING,
            'is_default'     => true,
            'is_shipping'    => true,
            'country_code'   => 'LT',
            'city'           => 'Vilnius',
            'address_line_1' => 'Gedimino pr. 1',
            'postal_code'    => '01103',
            'phone'          => $phoneNumber,
        ]);
    }

    public function billingAddress(): static
    {
        return $this->hasAddresses(1, fn (): array => [
            'type' => AddressType::BILLING,
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

    private function hasUsersColumn(string $column): bool
    {
        return $this->tableExists('users') && $this->tableHasColumn('users', $column);
    }
}
