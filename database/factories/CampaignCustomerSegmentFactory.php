<?php declare(strict_types=1);

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
        return [
            'campaign_id' => Campaign::factory(),
            'customer_group_id' => $this->faker->optional(0.7)->randomElement(CustomerGroup::pluck('id')->toArray()),
            'segment_type' => $this->faker->randomElement(['group', 'location', 'behavior', 'custom']),
            'segment_criteria' => [
                'age_range' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                'gender' => $this->faker->randomElement(['all', 'male', 'female']),
                'location' => $this->faker->country(),
                'purchase_history' => $this->faker->boolean(),
                'engagement_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
        ];
    }

    public function group(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_type' => 'group',
            'customer_group_id' => CustomerGroup::factory(),
            'segment_criteria' => [
                'group_type' => $this->faker->randomElement(['vip', 'regular', 'premium', 'wholesale']),
            ],
        ]);
    }

    public function location(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_type' => 'location',
            'customer_group_id' => null,
            'segment_criteria' => [
                'country' => $this->faker->country(),
                'region' => $this->faker->state(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
            ],
        ]);
    }

    public function behavior(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_type' => 'behavior',
            'customer_group_id' => null,
            'segment_criteria' => [
                'purchase_frequency' => $this->faker->randomElement(['low', 'medium', 'high']),
                'last_purchase' => $this->faker->randomElement(['1_month', '3_months', '6_months', '1_year']),
                'average_order_value' => $this->faker->randomElement(['0-50', '50-100', '100-500', '500+']),
                'engagement' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_type' => 'custom',
            'customer_group_id' => null,
            'segment_criteria' => [
                'custom_rules' => $this->faker->sentences(3),
                'conditions' => $this->faker->randomElements(['has_email', 'has_phone', 'newsletter_subscriber', 'social_login'], 2),
            ],
        ]);
    }
}
