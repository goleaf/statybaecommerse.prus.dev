<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignCustomerSegment;
use App\Models\Campaign;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignCustomerSegmentFactory extends Factory
{
    protected $model = CampaignCustomerSegment::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'customer_group_id' => CustomerGroup::factory(),
            'segment_type' => $this->faker->randomElement(['group', 'location', 'behavior', 'custom']),
            'segment_criteria' => [
                'min_age' => $this->faker->numberBetween(18, 30),
                'max_age' => $this->faker->numberBetween(31, 65),
                'gender' => $this->faker->randomElement(['male', 'female', 'all']),
                'location' => $this->faker->country(),
                'interests' => $this->faker->randomElements(['fashion', 'technology', 'sports', 'travel'], 2),
                'purchase_history' => $this->faker->randomElement(['new_customers', 'returning_customers', 'vip_customers']),
            ],
        ];
    }
}
