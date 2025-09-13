<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
final class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $types = ['warehouse', 'store', 'office', 'other'];
        $type = $this->faker->randomElement($types);

        return [
            'code' => strtoupper($this->faker->unique()->lexify('???###')),
            'name' => $this->faker->company() . ' ' . ucfirst($type),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'type' => $type,
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->optional()->state(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->randomElement(['LT', 'US', 'GB', 'DE', 'FR', 'CA', 'AU', 'BE', 'CH', 'AT', 'AE', 'AR', 'BG', 'BR', 'BY', 'CN', 'CZ', 'DK', 'EE', 'EG', 'ES', 'FI', 'HR', 'HU', 'ID', 'IL', 'IN', 'IT', 'JP', 'KE', 'KR', 'LV', 'MX', 'MY', 'NG', 'NL', 'NO', 'NZ', 'PH', 'PL', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'TH', 'TR', 'UA', 'VN', 'ZA']),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->companyEmail(),
            'latitude' => $this->faker->optional()->latitude(),
            'longitude' => $this->faker->optional()->longitude(),
            'opening_hours' => $this->faker->optional()->randomElement([
                null,
                [
                    ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day' => 'tuesday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day' => 'wednesday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day' => 'thursday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day' => 'friday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                    ['day' => 'saturday', 'open_time' => '10:00', 'close_time' => '15:00', 'is_closed' => false],
                    ['day' => 'sunday', 'open_time' => null, 'close_time' => null, 'is_closed' => true],
                ]
            ]),
            'contact_info' => $this->faker->optional()->randomElement([
                null,
                [
                    'manager' => $this->faker->name(),
                    'department' => $this->faker->randomElement(['Warehouse', 'Sales', 'Customer Service']),
                    'emergency_contact' => $this->faker->phoneNumber(),
                ]
            ]),
            'is_enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'is_default' => $this->faker->boolean(10), // 10% chance of being default
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'warehouse',
            'name' => $this->faker->company() . ' Warehouse',
        ]);
    }

    public function storeType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'store',
            'name' => $this->faker->company() . ' Store',
        ]);
    }

    public function office(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'office',
            'name' => $this->faker->company() . ' Office',
        ]);
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

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ]);
    }

    public function withOpeningHours(): static
    {
        return $this->state(fn (array $attributes) => [
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'tuesday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'wednesday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'thursday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'friday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'saturday', 'open_time' => '10:00', 'close_time' => '15:00', 'is_closed' => false],
                ['day' => 'sunday', 'open_time' => null, 'close_time' => null, 'is_closed' => true],
            ],
        ]);
    }

    public function withContactInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_info' => [
                'manager' => $this->faker->name(),
                'department' => $this->faker->randomElement(['Warehouse', 'Sales', 'Customer Service']),
                'emergency_contact' => $this->faker->phoneNumber(),
                'notes' => $this->faker->sentence(),
            ],
        ]);
    }

    public function inLithuania(): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => 'LT',
            'city' => $this->faker->randomElement(['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai', 'Panevėžys']),
        ]);
    }
}