<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignSchedule>
 */
final class CampaignScheduleFactory extends Factory
{
    protected $model = CampaignSchedule::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'schedule_type' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'custom']),
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => $this->faker->timezone(),
                'days' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 3),
                'frequency' => $this->faker->numberBetween(1, 7),
                'end_date' => $this->faker->optional(0.5)->dateTimeBetween('now', '+3 months'),
            ],
            'next_run_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'last_run_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    public function daily(): static
    {
        return $this->state(fn(array $attributes) => [
            'schedule_type' => 'daily',
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'UTC',
                'frequency' => 1,
            ],
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn(array $attributes) => [
            'schedule_type' => 'weekly',
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'UTC',
                'days' => [$this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
                'frequency' => 1,
            ],
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn(array $attributes) => [
            'schedule_type' => 'monthly',
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'UTC',
                'day_of_month' => $this->faker->numberBetween(1, 28),
                'frequency' => 1,
            ],
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn(array $attributes) => [
            'schedule_type' => 'custom',
            'schedule_config' => [
                'cron_expression' => $this->faker->randomElement([
                    '0 9 * * 1-5',  // Every weekday at 9 AM
                    '0 18 * * 1',  // Every Monday at 6 PM
                    '0 12 1 * *',  // First day of every month at 12 PM
                    '0 0 * * 0',  // Every Sunday at midnight
                ]),
                'timezone' => 'UTC',
            ],
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'next_run_at' => $this->faker->dateTimeBetween('now', '+1 week'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
            'next_run_at' => null,
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'next_run_at' => $this->faker->dateTimeBetween('now', '+1 day'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'next_run_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'last_run_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
        ]);
    }
}
