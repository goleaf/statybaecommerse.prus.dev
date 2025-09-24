<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

final class CampaignClickSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('en_US');

        // Get existing campaigns and users
        $campaigns = Campaign::all();
        $users = User::all();

        if ($campaigns->isEmpty() || $users->isEmpty()) {
            $this->command->info('No campaigns or users found. Skipping CampaignClick seeding.');

            return;
        }

        $clickTypes = ['cta', 'banner', 'link', 'button', 'image'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $browsers = ['chrome', 'firefox', 'safari', 'edge', 'opera'];
        $operatingSystems = ['windows', 'macos', 'linux', 'android', 'ios'];
        $countries = ['Lithuania', 'Latvia', 'Estonia', 'Poland', 'Germany', 'United States', 'United Kingdom'];

        $utmSources = ['google', 'facebook', 'twitter', 'instagram', 'linkedin', 'email', 'direct'];
        $utmMediums = ['cpc', 'social', 'email', 'organic', 'referral', 'display'];

        $this->command->info('Creating Campaign Clicks...');

        $progressBar = $this->command->getOutput()->createProgressBar(100);
        $progressBar->start();

        for ($i = 0; $i < 100; $i++) {
            $campaign = $campaigns->random();
            $user = $users->random();

            $clickType = $faker->randomElement($clickTypes);
            $deviceType = $faker->randomElement($deviceTypes);
            $browser = $faker->randomElement($browsers);
            $os = $faker->randomElement($operatingSystems);
            $country = $faker->randomElement($countries);

            $utmSource = $faker->randomElement($utmSources);
            $utmMedium = $faker->randomElement($utmMediums);

            $clickedAt = $faker->dateTimeBetween('-30 days', 'now');
            $isConverted = $faker->boolean(25);  // 25% conversion rate

            CampaignClick::create([
                'campaign_id' => $campaign->id,
                'customer_id' => $user->id,
                'clicked_url' => $faker->url(),
                'referer' => $faker->url(),
                'ip_address' => $faker->ipv4(),
                'user_agent' => $faker->userAgent(),
                'click_type' => $clickType,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'country' => $country,
                'session_id' => $faker->uuid(),
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $campaign->name,
                'utm_term' => $faker->words(2, true),
                'utm_content' => $faker->words(3, true),
                'clicked_at' => $clickedAt,
                'is_converted' => $isConverted,
                'conversion_value' => $isConverted ? $faker->randomFloat(2, 10, 500) : null,
                'conversion_currency' => 'EUR',
                'conversion_data' => $isConverted ? [
                    'order_id' => $faker->uuid(),
                    'product_name' => $faker->words(3, true),
                    'conversion_time' => $clickedAt->addMinutes($faker->numberBetween(1, 60)),
                ] : null,
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Campaign Clicks created successfully!');
    }
}
