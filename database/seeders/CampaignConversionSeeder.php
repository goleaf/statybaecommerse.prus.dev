<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use Illuminate\Database\Seeder;

final class CampaignConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::query()->get();

        if ($campaigns->isEmpty()) {
            $this->command?->warn('No campaigns found. Skipping CampaignConversion seeding.');

            return;
        }

        $campaigns->each(function (Campaign $campaign): void {
            if ($campaign->conversions()->exists()) {
                return;
            }

            $conversionsPerCampaign = fake()->numberBetween(10, 40);

            CampaignConversion::factory()
                ->count($conversionsPerCampaign)
                ->for($campaign)
                ->state(fn (): array => [
                    'campaign_name' => $campaign->name,
                    'converted_at' => now()->subDays(random_int(1, 30))->addMinutes(random_int(1, 600)),
                ])
                ->create();
        });

        CampaignConversion::factory()
            ->count(25)
            ->highValue()
            ->verified()
            ->attributed()
            ->create();

        $this->command?->info('Campaign conversions seeded successfully!');
    }
}
