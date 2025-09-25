<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Illuminate\Database\Seeder;

final class CampaignScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::query()->with('schedules')->get();

        if ($campaigns->isEmpty()) {
            $this->command?->warn('No campaigns found. Skipping CampaignSchedule seeding.');

            return;
        }

        $campaigns->each(function (Campaign $campaign): void {
            if ($campaign->schedules()->exists()) {
                return;
            }

            CampaignSchedule::factory()
                ->count(4)
                ->for($campaign)
                ->create();
        });

        CampaignSchedule::factory()
            ->count(10)
            ->create();

        $this->command?->info('Campaign schedules seeded successfully!');
    }
}
