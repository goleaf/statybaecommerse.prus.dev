<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class CampaignCustomerSegmentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have campaigns and customer groups
        $campaigns = $this->ensureRecords(Campaign::class, 10);
        $customerGroups = $this->ensureRecords(CustomerGroup::class, 5);

        // Define segment types with their characteristics
        $segmentTypes = [
            'demographic' => [
                'criteria_examples' => [
                    ['age_range' => '18-25', 'gender' => 'female'],
                    ['age_range' => '26-35', 'gender' => 'male'],
                    ['age_range' => '36-50', 'income_level' => 'high'],
                    ['age_range' => '51-65', 'education' => 'university'],
                ],
                'targeting_tags' => ['young_adults', 'middle_aged', 'high_income', 'educated'],
            ],
            'behavioral' => [
                'criteria_examples' => [
                    ['purchase_frequency' => 'high', 'loyalty_level' => 'vip'],
                    ['browsing_behavior' => 'frequent', 'cart_abandonment' => 'low'],
                    ['product_categories' => 'electronics', 'seasonal_buyer' => true],
                    ['discount_sensitivity' => 'high', 'brand_loyalty' => 'low'],
                ],
                'targeting_tags' => ['frequent_buyers', 'loyal_customers', 'tech_enthusiasts', 'price_sensitive'],
            ],
            'geographic' => [
                'criteria_examples' => [
                    ['country' => 'Lithuania', 'region' => 'Vilnius'],
                    ['country' => 'Lithuania', 'region' => 'Kaunas'],
                    ['country' => 'Latvia', 'region' => 'Riga'],
                    ['country' => 'Estonia', 'region' => 'Tallinn'],
                ],
                'targeting_tags' => ['lithuania', 'baltic_states', 'major_cities', 'regional'],
            ],
            'psychographic' => [
                'criteria_examples' => [
                    ['lifestyle' => 'urban', 'interests' => 'technology'],
                    ['lifestyle' => 'eco_conscious', 'values' => 'sustainability'],
                    ['personality' => 'adventurous', 'hobbies' => 'outdoor'],
                    ['personality' => 'conservative', 'preferences' => 'traditional'],
                ],
                'targeting_tags' => ['tech_savvy', 'eco_friendly', 'outdoor_lovers', 'traditional'],
            ],
        ];

        // Create segments for each campaign
        foreach ($campaigns as $campaign) {
            $segmentCount = fake()->numberBetween(1, 4);
            $usedGroups = collect();

            for ($i = 0; $i < $segmentCount; $i++) {
                // Select a random segment type
                $segmentType = fake()->randomElement(array_keys($segmentTypes));
                $typeConfig = $segmentTypes[$segmentType];

                // Get a customer group that hasn't been used for this campaign
                $availableGroups = $customerGroups->diff($usedGroups);
                $customerGroup = $availableGroups->isEmpty()
                    ? $customerGroups->random()
                    : $availableGroups->random();

                if ($customerGroup) {
                    $usedGroups->push($customerGroup);
                }

                // Generate criteria based on segment type
                $criteria = fake()->randomElement($typeConfig['criteria_examples']);

                // Add some additional random criteria
                $additionalCriteria = [];
                if ($segmentType === 'demographic') {
                    $additionalCriteria['location'] = fake()->city();
                    $additionalCriteria['occupation'] = fake()->jobTitle();
                } elseif ($segmentType === 'behavioral') {
                    $additionalCriteria['last_purchase_days'] = fake()->numberBetween(1, 365);
                    $additionalCriteria['total_orders'] = fake()->numberBetween(1, 50);
                } elseif ($segmentType === 'geographic') {
                    $additionalCriteria['timezone'] = fake()->timezone();
                    $additionalCriteria['language'] = fake()->randomElement(['lt', 'en', 'ru']);
                } elseif ($segmentType === 'psychographic') {
                    $additionalCriteria['social_media_usage'] = fake()->randomElement(['high', 'medium', 'low']);
                    $additionalCriteria['shopping_preference'] = fake()->randomElement(['online', 'offline', 'mixed']);
                }

                $finalCriteria = array_merge($criteria, $additionalCriteria);

                // Generate targeting tags
                $targetingTags = array_merge(
                    $typeConfig['targeting_tags'],
                    [fake()->word(), fake()->word()]
                );

                // Generate custom conditions
                $customConditions = $this->generateCustomConditions($segmentType);

                CampaignCustomerSegment::factory()->create([
                    'campaign_id' => $campaign->id,
                    'customer_group_id' => $customerGroup->id,
                    'segment_type' => $segmentType,
                    'segment_criteria' => $finalCriteria,
                    'targeting_tags' => $targetingTags,
                    'custom_conditions' => $customConditions,
                    'track_performance' => fake()->boolean(80),
                    'auto_optimize' => fake()->boolean(30),
                    'is_active' => fake()->boolean(85),
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // Create some special high-performance segments
        $this->createSpecialSegments($campaigns, $customerGroups);

        $this->command->info(sprintf(
            'Created %d campaign customer segments with comprehensive targeting data.',
            CampaignCustomerSegment::count()
        ));
    }

    private function ensureRecords(string $modelClass, int $minimum): Collection
    {
        $records = $modelClass::all();

        if ($records->count() >= $minimum) {
            return $records;
        }

        $missing = max(0, $minimum - $records->count());

        if ($missing > 0 && method_exists($modelClass, 'factory')) {
            try {
                $modelClass::factory()->count($missing)->create();

                return $modelClass::all();
            } catch (\Throwable $exception) {
                $this->command->warn("Could not create {$modelClass} records: ".$exception->getMessage());
            }
        }

        return $modelClass::all();
    }

    private function generateCustomConditions(string $segmentType): string
    {
        return match ($segmentType) {
            'demographic' => fake()->randomElement([
                'Age between 18-65 and income above average',
                'University education or higher',
                'Urban living with professional occupation',
                'Family status: married with children',
            ]),
            'behavioral' => fake()->randomElement([
                'Purchase frequency > 2 per month',
                'Cart abandonment rate < 20%',
                'Average order value > €50',
                'Last purchase within 30 days',
            ]),
            'geographic' => fake()->randomElement([
                'Located in major metropolitan areas',
                'Shipping zone: EU countries only',
                'Local time zone: GMT+2 or GMT+3',
                'Language preference: Lithuanian or English',
            ]),
            'psychographic' => fake()->randomElement([
                'High interest in technology and innovation',
                'Environmentally conscious lifestyle',
                'Active on social media platforms',
                'Prefers premium quality over price',
            ]),
            default => 'Custom segment conditions apply',
        };
    }

    private function createSpecialSegments(Collection $campaigns, Collection $customerGroups): void
    {
        // Create VIP customer segments
        $vipSegments = [
            [
                'segment_type' => 'behavioral',
                'segment_criteria' => [
                    'customer_tier' => 'vip',
                    'lifetime_value' => 'high',
                    'purchase_frequency' => 'very_high',
                    'loyalty_points' => 'premium',
                ],
                'targeting_tags' => ['vip', 'high_value', 'loyal'],
                'custom_conditions' => 'VIP customers with lifetime value > €1000',
                'track_performance' => true,
                'auto_optimize' => true,
            ],
            [
                'segment_type' => 'demographic',
                'segment_criteria' => [
                    'age_range' => '25-45',
                    'income_level' => 'high',
                    'education' => 'university',
                    'occupation' => 'professional',
                ],
                'targeting_tags' => ['professionals', 'high_income', 'educated'],
                'custom_conditions' => 'High-income professionals aged 25-45',
                'track_performance' => true,
                'auto_optimize' => true,
            ],
            [
                'segment_type' => 'psychographic',
                'segment_criteria' => [
                    'lifestyle' => 'luxury',
                    'brand_preference' => 'premium',
                    'shopping_behavior' => 'quality_focused',
                    'price_sensitivity' => 'low',
                ],
                'targeting_tags' => ['luxury', 'premium', 'quality_focused'],
                'custom_conditions' => 'Luxury lifestyle with premium brand preferences',
                'track_performance' => true,
                'auto_optimize' => true,
            ],
        ];

        foreach ($vipSegments as $segmentData) {
            $campaign = $campaigns->random();
            $customerGroup = $customerGroups->random();

            CampaignCustomerSegment::factory()->create(array_merge($segmentData, [
                'campaign_id' => $campaign->id,
                'customer_group_id' => $customerGroup->id,
                'is_active' => true,
                'sort_order' => 999,
            ]));
        }
    }
}
