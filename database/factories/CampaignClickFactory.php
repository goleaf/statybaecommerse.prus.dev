<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignClick>
 */
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
            'click_type' => $this->faker->randomElement(['cta', 'banner', 'link', 'button']),
            'clicked_url' => $this->faker->optional(0.8)->url(),
            'customer_id' => $this->faker->optional(0.4)->randomElement(User::pluck('id')->toArray()),
            'clicked_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function cta(): static
    {
        return $this->state(fn (array $attributes) => [
            'click_type' => 'cta',
            'clicked_url' => $this->faker->url(),
        ]);
    }

    public function banner(): static
    {
        return $this->state(fn (array $attributes) => [
            'click_type' => 'banner',
            'clicked_url' => $this->faker->url(),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => User::factory(),
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'user_agent' => $this->faker->userAgent().' Mobile',
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'user_agent' => $this->faker->userAgent(),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_converted' => true,
            'conversion_value' => $this->faker->randomFloat(2, 10, 1000),
        ]);
    }
}
