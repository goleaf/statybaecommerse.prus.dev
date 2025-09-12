<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignClick;
use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignClickFactory extends Factory
{
    protected $model = CampaignClick::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'click_type' => $this->faker->randomElement(['cta', 'banner', 'link']),
            'clicked_url' => $this->faker->url(),
            'customer_id' => $this->faker->optional(0.6)->numberBetween(1, 100),
            'clicked_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
