<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignConversion>
 */
final class CampaignConversionFactory extends Factory
{
    protected $model = CampaignConversion::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'click_id' => $this->faker->optional(0.8)->randomElement(CampaignClick::pluck('id')->toArray()),
            'order_id' => $this->faker->optional(0.7)->randomElement(Order::pluck('id')->toArray()),
            'customer_id' => $this->faker->optional(0.6)->randomElement(User::pluck('id')->toArray()),
            'conversion_type' => $this->faker->randomElement(['purchase', 'signup', 'download', 'registration']),
            'conversion_value' => $this->faker->randomFloat(2, 0, 1000),
            'session_id' => $this->faker->uuid(),
            'conversion_data' => [
                'source' => $this->faker->randomElement(['web', 'mobile', 'email', 'social']),
                'device' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            ],
            'converted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'purchase',
            'conversion_value' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'completed',
            'order_id' => Order::factory()->create([
                'channel_id' => null,
                'zone_id' => null,
                'partner_id' => null,
            ])->id,
        ]);
    }

    public function signup(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'signup',
            'conversion_value' => 0,
            'status' => 'completed',
            'customer_id' => User::factory(),
        ]);
    }

    public function download(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'download',
            'conversion_value' => 0,
            'conversion_data' => [
                'file_name' => $this->faker->word().'.pdf',
                'file_size' => $this->faker->numberBetween(1000, 10000000),
            ],
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'purchase',
            'conversion_value' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'converted_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => User::factory(),
        ]);
    }

    public function withClick(): static
    {
        return $this->state(fn (array $attributes) => [
            'click_id' => CampaignClick::factory(),
        ]);
    }

    public function withOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => Order::factory()->create([
                'channel_id' => null,
                'zone_id' => null,
                'partner_id' => null,
            ])->id,
            'conversion_type' => 'purchase',
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'is_mobile' => true,
            'is_tablet' => false,
            'is_desktop' => false,
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_desktop' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
