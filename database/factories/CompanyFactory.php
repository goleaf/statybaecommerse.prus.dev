<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyTypes = [
            'Statybos Centras', 'Lietuvos Statybos', 'Vilniaus Statybos',
            'Kauno Statybos', 'Klaipėdos Statybos', 'Panevėžio Statybos',
            'Šiaulių Statybos', 'Alytaus Statybos', 'Marijampolės Statybos',
            'Tauragės Statybos', 'UAB', 'MB', 'IĮ', 'VšĮ'
        ];

        return [
            'name' => fake()->randomElement($companyTypes) . ' ' . fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'website' => fake()->url(),
            'industry' => fake()->randomElement([
                'construction', 'manufacturing', 'technology', 'retail',
                'services', 'healthcare', 'education', 'finance'
            ]),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(80), // 80% chance to be active
            'metadata' => [
                'founded_year' => fake()->year(),
                'employee_count' => fake()->numberBetween(1, 1000),
                'revenue' => fake()->numberBetween(10000, 10000000),
                'certifications' => fake()->randomElements([
                    'ISO 9001', 'ISO 14001', 'OHSAS 18001', 'CE', 'FSC'
                ], fake()->numberBetween(0, 3)),
            ],
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

    public function construction(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'construction',
        ]);
    }

    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => 'small',
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => 'medium',
        ]);
    }

    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => 'large',
        ]);
    }
}
