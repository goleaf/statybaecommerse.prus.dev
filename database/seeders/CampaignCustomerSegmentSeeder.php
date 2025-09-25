<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use Illuminate\Database\Seeder;

final class CampaignCustomerSegmentSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::query()->with('customerSegments')->get();

        if ($campaigns->isEmpty()) {
            $this->command?->warn('No campaigns found. Skipping CampaignCustomerSegment seeding.');

            return;
        }

        $campaigns->each(function (Campaign $campaign): void {
            if ($campaign->customerSegments()->exists()) {
                return;
            }

            $segmentCount = fake()->numberBetween(1, 3);

            CampaignCustomerSegment::factory()
                ->count($segmentCount)
                ->for($campaign)
                ->create();
        });

        CampaignCustomerSegment::factory()
            ->count(5)
            ->highPerformance()
            ->create();

        $this->command?->info(sprintf(
            'Created %d campaign customer segments with comprehensive targeting data.',
            CampaignCustomerSegment::query()->count()
        ));
    }
}
