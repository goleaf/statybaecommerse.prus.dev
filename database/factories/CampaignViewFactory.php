<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignView;
use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignViewFactory extends Factory
{
    protected $model = CampaignView::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->optional(0.7)->url(),
            'customer_id' => $this->faker->optional(0.6)->randomElement(Customer::pluck('id')->toArray()),
            'viewed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
