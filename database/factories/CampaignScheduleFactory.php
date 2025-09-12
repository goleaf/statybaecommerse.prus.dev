<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignSchedule;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignScheduleFactory extends Factory
{
    protected $model = CampaignSchedule::class;

    public function definition(): array
    {
        $scheduleType = $this->faker->randomElement(['daily', 'weekly', 'monthly', 'custom']);
        $nextRunAt = $this->faker->dateTimeBetween('now', '+30 days');

        return [
            'campaign_id' => Campaign::factory(),
            'schedule_type' => $scheduleType,
            'schedule_config' => [
                'frequency' => $scheduleType,
                'time' => $this->faker->time('H:i'),
                'days' => $scheduleType === 'weekly' ? $this->faker->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 3) : null,
                'date' => $scheduleType === 'monthly' ? $this->faker->numberBetween(1, 28) : null,
                'timezone' => 'UTC',
            ],
            'next_run_at' => $nextRunAt,
            'last_run_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
