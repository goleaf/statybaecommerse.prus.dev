<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => [
                'lt' => $name.' LT',
                'en' => $name.' EN',
            ],
            'slug' => [
                'lt' => \Illuminate\Support\Str::slug($name.' LT'),
                'en' => \Illuminate\Support\Str::slug($name.' EN'),
            ],
            'description' => [
                'lt' => $this->faker->sentence(10),
                'en' => $this->faker->sentence(10),
            ],
            'code' => strtoupper($this->faker->unique()->lexify('LOC???')),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => 'LT', // Default to Lithuania
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'is_default' => false,
            'is_enabled' => true,
            'type' => $this->faker->randomElement(['warehouse', 'store', 'pickup_point']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the location is the default location.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the location is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Create a location in Vilnius.
     */
    public function vilnius(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => [
                'lt' => 'Vilniaus sandėlis',
                'en' => 'Vilnius Warehouse',
            ],
            'slug' => [
                'lt' => 'vilniaus-sandelis',
                'en' => 'vilnius-warehouse',
            ],
            'description' => [
                'lt' => 'Pagrindinis sandėlis Vilniuje',
                'en' => 'Main warehouse in Vilnius',
            ],
            'address_line_1' => 'Gedimino pr. 9',
            'city' => 'Vilnius',
            'state' => 'Vilnius County',
            'postal_code' => '01103',
            'country_code' => 'LT',
        ]);
    }

    /**
     * Create a location in Kaunas.
     */
    public function kaunas(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => [
                'lt' => 'Kauno parduotuvė',
                'en' => 'Kaunas Store',
            ],
            'slug' => [
                'lt' => 'kauno-parduotuve',
                'en' => 'kaunas-store',
            ],
            'description' => [
                'lt' => 'Parduotuvė Kaune',
                'en' => 'Store in Kaunas',
            ],
            'address_line_1' => 'Laisvės al. 53',
            'city' => 'Kaunas',
            'state' => 'Kaunas County',
            'postal_code' => '44309',
            'country_code' => 'LT',
            'type' => 'store',
        ]);
    }

    /**
     * Create a location with specific country.
     */
    public function forCountry(Country $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => $country->cca2,
        ]);
    }
}
