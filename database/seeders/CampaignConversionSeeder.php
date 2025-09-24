<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CampaignConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing campaigns and users
        $campaigns = Campaign::all();
        $users = User::all();

        if ($campaigns->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No campaigns or users found. Please run CampaignSeeder and UserSeeder first.');

            return;
        }

        // Create conversions for each campaign
        foreach ($campaigns as $campaign) {
            // Create 10-50 conversions per campaign
            $conversionCount = fake()->numberBetween(10, 50);

            for ($i = 0; $i < $conversionCount; $i++) {
                CampaignConversion::factory()
                    ->for($campaign)
                    ->for($users->random())
                    ->create([
                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,
                    ]);
            }
        }

        // Create some high-value conversions
        CampaignConversion::factory()
            ->count(20)
            ->highValue()
            ->verified()
            ->attributed()
            ->create();

        // Create some mobile conversions
        CampaignConversion::factory()
            ->count(30)
            ->mobile()
            ->create();

        // Create some tablet conversions
        CampaignConversion::factory()
            ->count(15)
            ->tablet()
            ->create();

        // Create some desktop conversions
        CampaignConversion::factory()
            ->count(40)
            ->desktop()
            ->create();

        // Create some recent conversions
        CampaignConversion::factory()
            ->count(25)
            ->recent()
            ->create();

        // Create some Google conversions
        CampaignConversion::factory()
            ->count(35)
            ->fromGoogle()
            ->create();

        // Create some Facebook conversions
        CampaignConversion::factory()
            ->count(20)
            ->fromFacebook()
            ->create();

        // Create some email conversions
        CampaignConversion::factory()
            ->count(15)
            ->fromEmail()
            ->create();

        // Create some purchase conversions
        CampaignConversion::factory()
            ->count(50)
            ->purchase()
            ->completed()
            ->create();

        // Create some signup conversions
        CampaignConversion::factory()
            ->count(30)
            ->signup()
            ->completed()
            ->create();

        // Create some pending conversions
        CampaignConversion::factory()
            ->count(20)
            ->pending()
            ->create();

        $this->command->info('Campaign conversions seeded successfully!');
    }
}
