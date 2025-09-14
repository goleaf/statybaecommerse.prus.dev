<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignView>
 */
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
            'customer_id' => $this->faker->optional(0.3)->randomElement(User::pluck('id')->toArray()),
            'viewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => Customer::factory(),
        ]);
    }

    public function withReferer(): static
    {
        return $this->state(fn (array $attributes) => [
            'referer' => $this->faker->randomElement([
                'https://google.com',
                'https://facebook.com',
                'https://twitter.com',
                'https://example.com',
            ]),
        ]);
    }
}
