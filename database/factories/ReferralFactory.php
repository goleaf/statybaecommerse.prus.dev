<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Referral>
 */
final class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'referral_code' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'status' => $this->faker->randomElement(['pending', 'completed', 'expired']),
            'completed_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 year', 'now'),
            'expires_at' => $this->faker->optional(0.7)->dateTimeBetween('now', '+1 year'),
            'metadata' => $this->faker->optional(0.5)->randomElements([
                'utm_source' => $this->faker->randomElement(['google', 'facebook', 'twitter', 'email']),
                'utm_medium' => $this->faker->randomElement(['cpc', 'social', 'email', 'organic']),
                'utm_campaign' => $this->faker->randomElement(['summer2024', 'winter2024', 'spring2024']),
                'device' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'browser' => $this->faker->randomElement(['chrome', 'firefox', 'safari', 'edge']),
            ]),
            'source' => $this->faker->randomElement(['website', 'email', 'social', 'mobile_app', 'partner']),
            'campaign' => $this->faker->optional(0.6)->randomElement(['summer2024', 'winter2024', 'spring2024', 'referral_bonus', 'new_user']),
            'utm_source' => $this->faker->optional(0.4)->randomElement(['google', 'facebook', 'twitter', 'linkedin']),
            'utm_medium' => $this->faker->optional(0.4)->randomElement(['cpc', 'social', 'email', 'organic']),
            'utm_campaign' => $this->faker->optional(0.4)->randomElement(['summer2024', 'winter2024', 'spring2024']),
            'ip_address' => $this->faker->optional(0.8)->ipv4(),
            'user_agent' => $this->faker->optional(0.8)->userAgent(),
            'title' => [
                'en' => $this->faker->sentence(3),
                'lt' => $this->faker->sentence(3),
            ],
            'description' => [
                'en' => $this->faker->paragraph(2),
                'lt' => $this->faker->paragraph(2),
            ],
            'terms_conditions' => [
                'en' => $this->faker->paragraphs(3, true),
                'lt' => $this->faker->paragraphs(3, true),
            ],
            'benefits_description' => [
                'en' => $this->faker->paragraph(2),
                'lt' => $this->faker->paragraph(2),
            ],
            'how_it_works' => [
                'en' => $this->faker->paragraphs(2, true),
                'lt' => $this->faker->paragraphs(2, true),
            ],
            'seo_title' => [
                'en' => $this->faker->sentence(4),
                'lt' => $this->faker->sentence(4),
            ],
            'seo_description' => [
                'en' => $this->faker->paragraph(1),
                'lt' => $this->faker->paragraph(1),
            ],
            'seo_keywords' => [
                'en' => $this->faker->words(5),
                'lt' => $this->faker->words(5),
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
        ]);
    }

    public function withRewards(): static
    {
        return $this->afterCreating(function (Referral $referral) {
            \App\Models\ReferralReward::factory()
                ->count($this->faker->numberBetween(1, 3))
                ->create(['referral_id' => $referral->id]);
        });
    }

    public function withOrders(): static
    {
        return $this->afterCreating(function (Referral $referral) {
            \App\Models\Order::factory()
                ->count($this->faker->numberBetween(1, 5))
                ->create(['user_id' => $referral->referred_id]);
        });
    }

    public function withAnalytics(): static
    {
        return $this->afterCreating(function (Referral $referral) {
            \App\Models\AnalyticsEvent::factory()
                ->count($this->faker->numberBetween(1, 10))
                ->create(['referral_id' => $referral->id]);
        });
    }
}
