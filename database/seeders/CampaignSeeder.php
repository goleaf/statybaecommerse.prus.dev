<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CampaignTranslation;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\Category;
use App\Models\Channel;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        // Create channels and zones if they don't exist
        $channels = collect();
        $zones = collect();
        $categories = collect();
        $products = collect();
        $customerGroups = collect();

        // Try to create or get existing records
        try {
            $channels = Channel::factory()->count(3)->create();
        } catch (\Exception $e) {
            $channels = collect(range(1, 3))->map(fn($i) => (object) ['id' => $i]);
        }

        try {
            $zones = Zone::factory()->count(5)->create();
        } catch (\Exception $e) {
            $zones = collect(range(1, 5))->map(fn($i) => (object) ['id' => $i]);
        }

        try {
            $categories = Category::factory()->count(10)->create();
        } catch (\Exception $e) {
            $categories = collect(range(1, 10))->map(fn($i) => (object) ['id' => $i]);
        }

        try {
            $products = Product::factory()->count(20)->create();
        } catch (\Exception $e) {
            $products = collect(range(1, 20))->map(fn($i) => (object) ['id' => $i]);
        }

        try {
            $customerGroups = CustomerGroup::factory()->count(5)->create();
        } catch (\Exception $e) {
            $customerGroups = collect(range(1, 5))->map(fn($i) => (object) ['id' => $i]);
        }

        // Create featured campaigns
        $featuredCampaigns = Campaign::factory()
            ->count(5)
            ->featured()
            ->active()
            ->highPerformance()
            ->create([
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]);

        // Create regular active campaigns
        $activeCampaigns = Campaign::factory()
            ->count(15)
            ->active()
            ->create([
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]);

        // Create scheduled campaigns
        $scheduledCampaigns = Campaign::factory()
            ->count(8)
            ->scheduled()
            ->create([
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]);

        // Create expired campaigns
        $expiredCampaigns = Campaign::factory()
            ->count(5)
            ->expired()
            ->create([
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]);

        // Create draft campaigns
        $draftCampaigns = Campaign::factory()
            ->count(7)
            ->create([
                'status' => 'draft',
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]);

        $allCampaigns = $featuredCampaigns
            ->concat($activeCampaigns)
            ->concat($scheduledCampaigns)
            ->concat($expiredCampaigns)
            ->concat($draftCampaigns);

        // Create campaign views for active and featured campaigns
        foreach ($featuredCampaigns->concat($activeCampaigns) as $campaign) {
            $viewCount = fake()->numberBetween(100, 5000);

            for ($i = 0; $i < $viewCount; $i++) {
                CampaignView::factory()->create([
                    'campaign_id' => $campaign->id,
                    'viewed_at' => fake()->dateTimeBetween($campaign->starts_at, now()),
                ]);
            }
        }

        // Create campaign clicks
        foreach ($featuredCampaigns->concat($activeCampaigns) as $campaign) {
            $clickCount = fake()->numberBetween(10, 500);

            for ($i = 0; $i < $clickCount; $i++) {
                CampaignClick::factory()->create([
                    'campaign_id' => $campaign->id,
                    'click_type' => fake()->randomElement(['cta', 'banner', 'link']),
                    'clicked_at' => fake()->dateTimeBetween($campaign->starts_at, now()),
                ]);
            }
        }

        // Create campaign conversions
        foreach ($featuredCampaigns->concat($activeCampaigns) as $campaign) {
            $conversionCount = fake()->numberBetween(5, 100);

            for ($i = 0; $i < $conversionCount; $i++) {
                CampaignConversion::factory()->create([
                    'campaign_id' => $campaign->id,
                    'conversion_type' => fake()->randomElement(['purchase', 'signup', 'download']),
                    'conversion_value' => fake()->randomFloat(2, 10, 500),
                    'converted_at' => fake()->dateTimeBetween($campaign->starts_at, now()),
                ]);
            }
        }

        // Create customer segments for campaigns
        foreach ($allCampaigns as $campaign) {
            $segmentCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $segmentCount; $i++) {
                CampaignCustomerSegment::factory()->create([
                    'campaign_id' => $campaign->id,
                    'customer_group_id' => $customerGroups->random()->id,
                    'segment_type' => fake()->randomElement(['group', 'location', 'behavior', 'custom']),
                ]);
            }
        }

        // Create product targets for campaigns
        foreach ($allCampaigns as $campaign) {
            $targetCount = fake()->numberBetween(2, 8);

            for ($i = 0; $i < $targetCount; $i++) {
                CampaignProductTarget::factory()->create([
                    'campaign_id' => $campaign->id,
                    'product_id' => $products->random()->id,
                    'category_id' => $categories->random()->id,
                    'target_type' => fake()->randomElement(['product', 'category', 'brand', 'collection']),
                ]);
            }
        }

        // Create schedules for some campaigns
        foreach ($allCampaigns->random(10) as $campaign) {
            CampaignSchedule::factory()->create([
                'campaign_id' => $campaign->id,
                'schedule_type' => fake()->randomElement(['daily', 'weekly', 'monthly', 'custom']),
                'is_active' => fake()->boolean(80),
            ]);
        }

        // Create special seasonal campaigns
        $seasonalCampaigns = [
            [
                'name' => 'Summer Sale 2024',
                'slug' => 'summer-sale-2024',
                'status' => 'active',
                'is_featured' => true,
                'display_priority' => 10,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'banner_image' => 'summer-sale-banner.jpg',
                'cta_text' => 'Shop Summer Collection',
                'cta_url' => '/collections/summer',
                'meta_title' => 'Summer Sale 2024 - Up to 50% Off',
                'meta_description' => 'Discover amazing summer deals with up to 50% off on selected items. Limited time offer!',
                'target_audience' => [
                    'age_range' => '18-45',
                    'gender' => 'all',
                    'interests' => ['fashion', 'summer', 'outdoor'],
                ],
            ],
            [
                'name' => 'Black Friday 2024',
                'slug' => 'black-friday-2024',
                'status' => 'scheduled',
                'is_featured' => true,
                'display_priority' => 10,
                'starts_at' => now()->addDays(30),
                'ends_at' => now()->addDays(33),
                'banner_image' => 'black-friday-banner.jpg',
                'cta_text' => 'Get Early Access',
                'cta_url' => '/black-friday',
                'meta_title' => 'Black Friday 2024 - Biggest Sale of the Year',
                'meta_description' => "Don't miss our biggest sale of the year! Massive discounts on all products.",
                'target_audience' => [
                    'age_range' => 'all',
                    'gender' => 'all',
                    'interests' => ['shopping', 'deals', 'electronics'],
                ],
            ],
            [
                'name' => 'New Year Special',
                'slug' => 'new-year-special-2024',
                'status' => 'scheduled',
                'is_featured' => true,
                'display_priority' => 9,
                'starts_at' => now()->addDays(60),
                'ends_at' => now()->addDays(67),
                'banner_image' => 'new-year-banner.jpg',
                'cta_text' => 'Start Fresh',
                'cta_url' => '/new-year',
                'meta_title' => 'New Year Special - Fresh Start with New Products',
                'meta_description' => 'Start the new year with our special collection of fresh and innovative products.',
                'target_audience' => [
                    'age_range' => '25-55',
                    'gender' => 'all',
                    'interests' => ['lifestyle', 'wellness', 'home'],
                ],
            ],
        ];

        $locales = $this->supportedLocales();

        foreach ($seasonalCampaigns as $campaignData) {
            // Extract translation data
            $translations = $campaignData['translations'] ?? [];
            unset($campaignData['translations']);

            $campaign = Campaign::factory()->create(array_merge($campaignData, [
                'channel_id' => $channels->random()->id,
                'zone_id' => $zones->random()->id,
            ]));

            // Create translations for each locale
            foreach ($locales as $locale) {
                $translationData = $translations[$locale] ?? [];
                CampaignTranslation::updateOrCreate([
                    'campaign_id' => $campaign->id,
                    'locale' => $locale,
                ], [
                    'name' => $translationData['name'] ?? $campaignData['name'] ?? 'Campaign',
                    'cta_text' => $translationData['cta_text'] ?? $campaignData['cta_text'] ?? 'Learn More',
                    'meta_title' => $translationData['meta_title'] ?? $campaignData['meta_title'] ?? '',
                    'meta_description' => $translationData['meta_description'] ?? $campaignData['meta_description'] ?? '',
                ]);
            }

            // Add analytics data for active campaigns
            if ($campaign->status === 'active') {
                $campaign->update([
                    'total_views' => fake()->numberBetween(1000, 10000),
                    'total_clicks' => fake()->numberBetween(100, 1000),
                    'total_conversions' => fake()->numberBetween(20, 200),
                    'total_revenue' => fake()->randomFloat(2, 2000, 20000),
                    'conversion_rate' => fake()->randomFloat(2, 2, 20),
                ]);
            }
        }

        $this->command->info('Created ' . $allCampaigns->count() . ' campaigns with full analytics and targeting data.');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
