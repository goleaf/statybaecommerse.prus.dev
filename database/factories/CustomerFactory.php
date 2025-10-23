<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'email'       => $this->faker->unique()->safeEmail(),
            'phone'       => $this->faker->phoneNumber(),
            'address'     => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'is_active'   => true,
            'metadata'    => [
                'preferred_language' => $this->faker->randomElement(['lt', 'en']),
                'note'               => $this->faker->sentence(),
            ],
            'country_id' => Schema::hasTable('countries') ? Country::factory() : null,
            'city_id'    => Schema::hasTable('cities') ? City::factory() : null,
            'company_id' => Schema::hasTable('companies') ? Company::factory() : null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
