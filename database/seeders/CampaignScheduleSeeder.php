<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ScheduleType;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Illuminate\Database\Seeder;

class CampaignScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::all();

        if ($campaigns->isEmpty()) {
            $this->command->warn('No campaigns found. Creating sample campaigns first...');

            return;
        }

        foreach ($campaigns as $campaign) {
            // Create daily schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::DAILY->value,
                'schedule_config' => [
                    'time' => '09:00',
                    'timezone' => 'Europe/Vilnius',
                    'frequency' => 'every_day',
                ],
                'next_run_at' => now()->addDay(),
                'is_active' => true,
            ]);

            // Create weekly schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::WEEKLY->value,
                'schedule_config' => [
                    'day' => 'monday',
                    'time' => '10:00',
                    'timezone' => 'Europe/Vilnius',
                    'frequency' => 'every_week',
                ],
                'next_run_at' => now()->addWeek(),
                'is_active' => false,
            ]);

            // Create monthly schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::MONTHLY->value,
                'schedule_config' => [
                    'day' => 1,
                    'time' => '08:00',
                    'timezone' => 'Europe/Vilnius',
                    'frequency' => 'every_month',
                ],
                'next_run_at' => now()->addMonth(),
                'is_active' => true,
            ]);

            // Create one-time schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::ONCE->value,
                'schedule_config' => [
                    'time' => '12:00',
                    'timezone' => 'Europe/Vilnius',
                    'frequency' => 'one_time',
                ],
                'next_run_at' => now()->addDays(3),
                'is_active' => true,
            ]);

            // Create custom schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::CUSTOM->value,
                'schedule_config' => [
                    'time' => '14:00',
                    'timezone' => 'Europe/Vilnius',
                    'days' => ['tuesday', 'thursday', 'saturday'],
                    'frequency' => 'custom',
                    'custom_rules' => [
                        'skip_holidays' => true,
                        'max_runs' => 10,
                        'end_date' => now()->addMonths(3)->toDateString(),
                    ],
                ],
                'next_run_at' => now()->addDays(2),
                'is_active' => false,
            ]);
        }

        // Create additional schedules with factory
        CampaignSchedule::factory(15)->create();

        $this->command->info('Campaign schedules seeded successfully!');
    }
}
