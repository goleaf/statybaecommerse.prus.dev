<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
final class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $dateRange = fake()->randomElement(['today', 'yesterday', 'last_7_days', 'last_30_days', 'last_90_days', 'this_year']);

        return [
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(['sales', 'products', 'customers', 'inventory']),
            'date_range' => $dateRange,
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->toDateString(),
            'filters' => [
                'status' => fake()->randomElement(['all', 'paid', 'pending']),
            ],
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
