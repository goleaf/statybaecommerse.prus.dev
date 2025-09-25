<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignClick;
use Illuminate\Database\Seeder;

final class CampaignClickSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::query()->with(['clicks', 'conversions'])->get();

        if ($campaigns->isEmpty()) {
            $this->command?->warn('No campaigns found. Skipping CampaignClick seeding.');

            return;
        }

        $campaigns->each(function (Campaign $campaign): void {
            if ($campaign->clicks()->exists()) {
                return;
            }

            $clicksPerCampaign = 25;

            CampaignClick::factory()
                ->count($clicksPerCampaign)
                ->for($campaign)
                ->withCustomer()
                ->state(function () use ($campaign): array {
                    return [
                        'utm_campaign' => $campaign->name,
                        'clicked_at' => now()->subDays(random_int(0, 30)),
                    ];
                })
                ->create();
        });
    }
}
