<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignCustomerSegment>
 */
final class CampaignCustomerSegmentFactory extends Factory
{
    protected $model = CampaignCustomerSegment::class;

    public function definition(): array
    {
        $segmentTypes = ['demographic', 'behavioral', 'geographic', 'psychographic'];
        $segmentType = fake()->randomElement($segmentTypes);

        return [
            'campaign_id' => Campaign::factory(),
            'customer_group_id' => CustomerGroup::factory(),
            'segment_type' => $segmentType,
            'segment_criteria' => $this->generateSegmentCriteria($segmentType),
            'targeting_tags' => $this->generateTargetingTags($segmentType),
            'custom_conditions' => $this->generateCustomConditions($segmentType),
            'track_performance' => fake()->boolean(80),
            'auto_optimize' => fake()->boolean(30),
            'is_active' => fake()->boolean(85),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function demographic(): static
    {
        return $this->state(fn (array $attributes) => [
            'segment_type' => 'demographic',
            'segment_criteria' => $this->generateDemographicCriteria(),
            'targeting_tags' => ['age_group', 'gender', 'income_level', 'education'],
            'custom_conditions' => fake()->randomElement([
                'Age between 18-65 and income above average',
                'University education or higher',
                'Urban living with professional occupation',
                'Family status: married with children',
            ]),
        ]);
    }

    public function behavioral(): static
    {
        return $this->state(fn (array $attributes) => [
            'segment_type' => 'behavioral',
            'segment_criteria' => $this->generateBehavioralCriteria(),
            'targeting_tags' => ['purchase_frequency', 'loyalty_level', 'browsing_behavior'],
            'custom_conditions' => fake()->randomElement([
                'Purchase frequency > 2 per month',
                'Cart abandonment rate < 20%',
                'Average order value > €50',
                'Last purchase within 30 days',
            ]),
        ]);
    }

    public function geographic(): static
    {
        return $this->state(fn (array $attributes) => [
            'segment_type' => 'geographic',
            'segment_criteria' => $this->generateGeographicCriteria(),
            'targeting_tags' => ['country', 'region', 'city', 'timezone'],
            'custom_conditions' => fake()->randomElement([
                'Located in major metropolitan areas',
                'Shipping zone: EU countries only',
                'Local time zone: GMT+2 or GMT+3',
                'Language preference: Lithuanian or English',
            ]),
        ]);
    }

    public function psychographic(): static
    {
        return $this->state(fn (array $attributes) => [
            'segment_type' => 'psychographic',
            'segment_criteria' => $this->generatePsychographicCriteria(),
            'targeting_tags' => ['lifestyle', 'interests', 'values', 'personality'],
            'custom_conditions' => fake()->randomElement([
                'High interest in technology and innovation',
                'Environmentally conscious lifestyle',
                'Active on social media platforms',
                'Prefers premium quality over price',
            ]),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_performance' => true,
            'auto_optimize' => true,
            'is_active' => true,
        ]);
    }

    private function generateSegmentCriteria(string $segmentType): array
    {
        return match ($segmentType) {
            'demographic' => $this->generateDemographicCriteria(),
            'behavioral' => $this->generateBehavioralCriteria(),
            'geographic' => $this->generateGeographicCriteria(),
            'psychographic' => $this->generatePsychographicCriteria(),
            default => ['custom' => 'generic_segment'],
        };
    }

    private function generateDemographicCriteria(): array
    {
        return [
            'age_range' => fake()->randomElement(['18-25', '26-35', '36-50', '51-65', '65+']),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'income_level' => fake()->randomElement(['low', 'medium', 'high', 'very_high']),
            'education' => fake()->randomElement(['high_school', 'bachelor', 'master', 'phd']),
            'occupation' => fake()->jobTitle(),
            'location' => fake()->city(),
        ];
    }

    private function generateBehavioralCriteria(): array
    {
        return [
            'purchase_frequency' => fake()->randomElement(['low', 'medium', 'high', 'very_high']),
            'loyalty_level' => fake()->randomElement(['new', 'regular', 'loyal', 'vip']),
            'browsing_behavior' => fake()->randomElement(['casual', 'frequent', 'intensive']),
            'cart_abandonment' => fake()->randomElement(['low', 'medium', 'high']),
            'last_purchase_days' => fake()->numberBetween(1, 365),
            'total_orders' => fake()->numberBetween(1, 100),
            'average_order_value' => fake()->randomFloat(2, 10, 500),
        ];
    }

    private function generateGeographicCriteria(): array
    {
        return [
            'country' => fake()->randomElement(['Lithuania', 'Latvia', 'Estonia', 'Poland', 'Germany']),
            'region' => fake()->randomElement(['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai', 'Panevėžys']),
            'city' => fake()->city(),
            'timezone' => fake()->randomElement(['GMT+2', 'GMT+3']),
            'language' => fake()->randomElement(['lt', 'en', 'ru', 'de']),
            'currency' => 'EUR',
        ];
    }

    private function generatePsychographicCriteria(): array
    {
        return [
            'lifestyle' => fake()->randomElement(['urban', 'suburban', 'rural', 'luxury', 'eco_conscious']),
            'interests' => fake()->randomElements([
                'technology', 'fashion', 'sports', 'travel', 'food', 'music', 'art', 'books',
            ], fake()->numberBetween(1, 4)),
            'values' => fake()->randomElements([
                'sustainability', 'innovation', 'tradition', 'quality', 'price', 'convenience',
            ], fake()->numberBetween(1, 3)),
            'personality' => fake()->randomElement(['adventurous', 'conservative', 'social', 'independent']),
            'shopping_preference' => fake()->randomElement(['online', 'offline', 'mixed']),
            'social_media_usage' => fake()->randomElement(['high', 'medium', 'low']),
        ];
    }

    private function generateTargetingTags(string $segmentType): array
    {
        $baseTags = match ($segmentType) {
            'demographic' => ['age_group', 'gender', 'income', 'education'],
            'behavioral' => ['frequent_buyers', 'loyal_customers', 'high_value'],
            'geographic' => ['location', 'region', 'timezone'],
            'psychographic' => ['lifestyle', 'interests', 'values'],
            default => ['general'],
        };

        return array_merge($baseTags, [
            fake()->word(),
            fake()->word(),
        ]);
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
}
