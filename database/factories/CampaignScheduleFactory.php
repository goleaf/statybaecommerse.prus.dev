<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ScheduleType;
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
            'schedule_type' => $this->faker->randomElement(ScheduleType::cases())->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => $this->faker->randomElement(['UTC', 'Europe/Vilnius', 'America/New_York']),
                'days' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], 3),
                'frequency' => $this->faker->randomElement(['every_day', 'every_week', 'every_month', 'custom']),
            ],
            'next_run_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'last_run_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => ScheduleType::DAILY->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'Europe/Vilnius',
                'frequency' => 'every_day',
            ],
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => ScheduleType::WEEKLY->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'Europe/Vilnius',
                'day' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'frequency' => 'every_week',
            ],
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => ScheduleType::MONTHLY->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'Europe/Vilnius',
                'day' => $this->faker->numberBetween(1, 28),
                'frequency' => 'every_month',
            ],
        ]);
    }

    public function once(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => ScheduleType::ONCE->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'Europe/Vilnius',
                'frequency' => 'one_time',
            ],
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => ScheduleType::CUSTOM->value,
            'schedule_config' => [
                'time' => $this->faker->time('H:i'),
                'timezone' => 'Europe/Vilnius',
                'days' => $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 3),
                'frequency' => 'custom',
                'custom_rules' => [
                    'skip_holidays' => $this->faker->boolean(),
                    'max_runs' => $this->faker->numberBetween(1, 50),
                ],
            ],
        ]);
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

    public function dueForExecution(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_run_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'is_active' => true,
        ]);
    }

    public function recentlyExecuted(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_run_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
