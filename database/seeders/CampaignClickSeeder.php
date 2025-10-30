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
            // Creating a small batch of campaigns guarantees that the click factory
            // runs even in freshly provisioned databases where no marketing data
            // has been loaded yet, mirroring the behaviour expected by "make all".
            Campaign::factory()
                ->count(5)
                ->create();

            $campaigns = Campaign::query()->with(['clicks', 'conversions'])->get();
        }

        $campaigns->each(function (Campaign $campaign): void {
            $clicksPerCampaign = 25;
            $existingClickCount = $campaign->clicks()->count();

            if ($existingClickCount >= $clicksPerCampaign) {
                return;
            }

            $missingClickCount = $clicksPerCampaign - $existingClickCount;

            CampaignClick::factory()
                // Only seed the amount required to reach the deterministic baseline
                // so repeated seeder runs stay idempotent while still producing data.
                ->count($missingClickCount)
                ->for($campaign)
                ->withCustomer()
                ->state(function () use ($campaign): array {
                    return [
                        'utm_campaign' => $campaign->name,
                        'clicked_at'   => now()->subDays(random_int(0, 30)),
                    ];
                })
                ->create();
        });
    }
}
