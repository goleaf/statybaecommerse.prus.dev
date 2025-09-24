<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignConversion>
 */
final class CampaignConversionFactory extends Factory
{
    protected $model = CampaignConversion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'customer_id' => User::factory(),
            'conversion_type' => $this->faker->randomElement(['purchase', 'signup', 'download', 'subscription', 'lead', 'custom']),
            'conversion_value' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['completed', 'pending', 'confirmed', 'cancelled']),
            'converted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'session_id' => $this->faker->uuid(),
            'conversion_data' => [
                'source' => $this->faker->randomElement(['google', 'facebook', 'twitter', 'linkedin']),
                'campaign_name' => $this->faker->words(3, true),
                'referrer' => $this->faker->url(),
            ],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement(['mobile', 'tablet', 'desktop']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'country' => $this->faker->countryCode(),
            'city' => $this->faker->city(),
            'is_mobile' => $this->faker->boolean(30),
            'is_tablet' => $this->faker->boolean(10),
            'is_desktop' => $this->faker->boolean(60),
            'conversion_duration' => $this->faker->numberBetween(10, 3600),
            'page_views' => $this->faker->numberBetween(1, 20),
            'time_on_site' => $this->faker->numberBetween(30, 1800),
            'bounce_rate' => $this->faker->randomFloat(2, 0, 1),
            'exit_page' => $this->faker->url(),
            'landing_page' => $this->faker->url(),
            'funnel_step' => $this->faker->randomElement(['awareness', 'interest', 'consideration', 'purchase', 'retention']),
            'attribution_model' => $this->faker->randomElement(['last_click', 'first_click', 'linear', 'time_decay', 'position_based', 'data_driven']),
            'conversion_path' => [
                'touchpoints' => $this->faker->numberBetween(1, 5),
                'channels' => $this->faker->randomElements(['google', 'facebook', 'email', 'direct'], 2),
            ],
            'touchpoints' => [
                'first_touch' => $this->faker->dateTimeBetween('-90 days', '-1 day'),
                'last_touch' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ],
            'last_click_attribution' => $this->faker->randomFloat(2, 0, 100),
            'first_click_attribution' => $this->faker->randomFloat(2, 0, 100),
            'linear_attribution' => $this->faker->randomFloat(2, 0, 100),
            'time_decay_attribution' => $this->faker->randomFloat(2, 0, 100),
            'position_based_attribution' => $this->faker->randomFloat(2, 0, 100),
            'data_driven_attribution' => $this->faker->randomFloat(2, 0, 100),
            'conversion_window' => $this->faker->numberBetween(1, 90),
            'lookback_window' => $this->faker->numberBetween(30, 365),
            'assisted_conversions' => $this->faker->numberBetween(0, 5),
            'assisted_conversion_value' => $this->faker->randomFloat(2, 0, 500),
            'total_conversions' => $this->faker->numberBetween(1, 10),
            'total_conversion_value' => $this->faker->randomFloat(2, 50, 2000),
            'conversion_rate' => $this->faker->randomFloat(4, 0, 1),
            'cost_per_conversion' => $this->faker->randomFloat(2, 5, 100),
            'roi' => $this->faker->randomFloat(4, -0.5, 5),
            'roas' => $this->faker->randomFloat(4, 0, 10),
            'lifetime_value' => $this->faker->randomFloat(2, 100, 5000),
            'customer_acquisition_cost' => $this->faker->randomFloat(2, 10, 200),
            'payback_period' => $this->faker->numberBetween(1, 365),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'tags' => $this->faker->optional(0.4)->randomElements(['high-value', 'new-customer', 'repeat-purchase', 'seasonal'], 2),
            'custom_attributes' => [
                'source_campaign' => $this->faker->words(2, true),
                'ad_group' => $this->faker->words(2, true),
                'keyword' => $this->faker->words(1, true),
            ],
        ];
    }

    /**
     * Indicate that the conversion is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the conversion is attributed.
     */
    public function attributed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_attributed' => true,
        ]);
    }

    /**
     * Indicate that the conversion is high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_value' => $this->faker->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * Indicate that the conversion is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'converted_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the conversion is from mobile device.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'is_mobile' => true,
            'is_tablet' => false,
            'is_desktop' => false,
        ]);
    }

    /**
     * Indicate that the conversion is from tablet device.
     */
    public function tablet(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'tablet',
            'is_mobile' => false,
            'is_tablet' => true,
            'is_desktop' => false,
        ]);
    }

    /**
     * Indicate that the conversion is from desktop device.
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_desktop' => true,
        ]);
    }

    /**
     * Indicate that the conversion is a purchase.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'purchase',
            'conversion_value' => $this->faker->randomFloat(2, 50, 1000),
        ]);
    }

    /**
     * Indicate that the conversion is a signup.
     */
    public function signup(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversion_type' => 'signup',
            'conversion_value' => 0,
        ]);
    }

    /**
     * Indicate that the conversion is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the conversion is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the conversion is from Google.
     */
    public function fromGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => $this->faker->words(2, true),
        ]);
    }

    /**
     * Indicate that the conversion is from Facebook.
     */
    public function fromFacebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'utm_source' => 'facebook',
            'utm_medium' => 'social',
            'utm_campaign' => $this->faker->words(2, true),
        ]);
    }

    /**
     * Indicate that the conversion is from email.
     */
    public function fromEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'utm_source' => 'email',
            'utm_medium' => 'email',
            'utm_campaign' => $this->faker->words(2, true),
        ]);
    }
}
