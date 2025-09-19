<?php

namespace Database\Seeders;

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

        foreach ($campaigns as $campaign) {
            // Create daily schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => 'daily',
                'schedule_config' => [
                    'time' => '09:00',
                    'timezone' => 'Europe/Vilnius',
                ],
                'next_run_at' => now()->addDay(),
                'is_active' => true,
            ]);

            // Create weekly schedule
            CampaignSchedule::create([
                'campaign_id' => $campaign->id,
                'schedule_type' => 'weekly',
                'schedule_config' => [
                    'day' => 'monday',
                    'time' => '10:00',
                    'timezone' => 'Europe/Vilnius',
                ],
                'next_run_at' => now()->addWeek(),
                'is_active' => false,
            ]);
        }

        // Create additional schedules
        CampaignSchedule::factory(10)->create();
    }
}
