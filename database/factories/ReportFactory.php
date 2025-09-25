<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Report>
 */
final class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $dateRange = fake()->randomElement(['today', 'yesterday', 'last_7_days', 'last_30_days', 'last_90_days', 'this_year']);
        $name = fake()->sentence(3);
        $type = fake()->randomElement(['sales', 'inventory', 'customer', 'product', 'analytics', 'financial', 'custom']);
        $category = fake()->randomElement(['sales', 'marketing', 'operations', 'finance', 'customer_service', 'inventory', 'analytics']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $type,
            'category' => $category,
            'date_range' => $dateRange,
            'start_date' => $dateRange === 'custom' ? now()->subDays(30) : null,
            'end_date' => $dateRange === 'custom' ? now() : null,
            'filters' => [
                'status' => fake()->randomElement(['all', 'paid', 'pending']),
                'category' => fake()->randomElement(['electronics', 'clothing', 'books']),
            ],
            'description' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'is_active' => fake()->boolean(80),
            'is_public' => fake()->boolean(60),
            'is_scheduled' => fake()->boolean(30),
            'schedule_frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'yearly']),
            'last_generated_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'generated_by' => fake()->optional(0.7)->randomElement(User::pluck('id')->toArray()),
            'view_count' => fake()->numberBetween(0, 1000),
            'download_count' => fake()->numberBetween(0, 100),
            'settings' => [
                'format' => fake()->randomElement(['pdf', 'excel', 'csv']),
                'include_charts' => fake()->boolean(),
                'include_summary' => fake()->boolean(),
            ],
            'metadata' => [
                'version' => '1.0',
                'author' => fake()->name(),
                'tags' => fake()->words(3),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_scheduled' => true,
            'schedule_frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
        ]);
    }

    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_generated_at' => now(),
            'generated_by' => User::factory(),
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => fake()->numberBetween(500, 5000),
            'download_count' => fake()->numberBetween(50, 500),
        ]);
    }
}
