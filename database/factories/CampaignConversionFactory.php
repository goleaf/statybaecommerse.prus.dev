<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignConversion;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignConversionFactory extends Factory
{
    protected $model = CampaignConversion::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'order_id' => $this->faker->optional(0.8)->randomElement(Order::pluck('id')->toArray()),
            'customer_id' => $this->faker->optional(0.7)->numberBetween(1, 100),
            'conversion_type' => $this->faker->randomElement(['purchase', 'signup', 'download', 'subscription']),
            'conversion_value' => $this->faker->randomFloat(2, 10, 1000),
            'session_id' => $this->faker->uuid(),
            'conversion_data' => [
                'source' => $this->faker->randomElement(['email', 'social', 'search', 'direct']),
                'device' => $this->faker->randomElement(['mobile', 'desktop', 'tablet']),
                'browser' => $this->faker->randomElement(['chrome', 'firefox', 'safari', 'edge']),
            ],
            'converted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
