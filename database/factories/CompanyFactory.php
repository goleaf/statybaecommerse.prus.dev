<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'website' => fake()->url(),
            'industry' => fake()->randomElement([
                'Technology',
                'Healthcare',
                'Finance',
                'Education',
                'Manufacturing',
                'Retail',
                'Real Estate',
                'Consulting',
                'Media',
                'Transportation'
            ]),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(80),  // 80% chance of being active
            'metadata' => [
                'founded_year' => fake()->year(),
                'employee_count' => fake()->numberBetween(1, 10000),
                'revenue' => fake()->numberBetween(100000, 10000000),
            ],
        ];
    }

    /**
     * Indicate that the company is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the company is small.
     */
    public function small(): static
    {
        return $this->state(fn(array $attributes) => [
            'size' => 'small',
        ]);
    }

    /**
     * Indicate that the company is medium.
     */
    public function medium(): static
    {
        return $this->state(fn(array $attributes) => [
            'size' => 'medium',
        ]);
    }

    /**
     * Indicate that the company is large.
     */
    public function large(): static
    {
        return $this->state(fn(array $attributes) => [
            'size' => 'large',
        ]);
    }

    /**
     * Indicate that the company is in technology industry.
     */
    public function technology(): static
    {
        return $this->state(fn(array $attributes) => [
            'industry' => 'Technology',
        ]);
    }

    /**
     * Indicate that the company is in healthcare industry.
     */
    public function healthcare(): static
    {
        return $this->state(fn(array $attributes) => [
            'industry' => 'Healthcare',
        ]);
    }
}
