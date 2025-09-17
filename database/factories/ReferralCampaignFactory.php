<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ReferralCampaignFactory extends Factory
{
    protected $model = ReferralCampaign::class;

    public function definition(): array
    {
        return [
            'name' => [
                'lt' => $this->faker->sentence(3),
                'en' => $this->faker->sentence(3),
            ],
            'description' => [
                'lt' => $this->faker->optional(0.8)->paragraph(),
                'en' => $this->faker->optional(0.8)->paragraph(),
            ],
            'is_active' => $this->faker->boolean(70),
            'start_date' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->optional(0.6)->dateTimeBetween('+1 month', '+1 year'),
            'reward_amount' => $this->faker->optional(0.9)->randomFloat(2, 5, 50),
            'reward_type' => $this->faker->randomElement(['percentage', 'fixed', 'points']),
            'max_referrals_per_user' => $this->faker->optional(0.5)->numberBetween(1, 10),
            'max_total_referrals' => $this->faker->optional(0.3)->numberBetween(100, 10000),
            'conditions' => $this->faker->optional(0.4)->randomElements([
                ['field' => 'min_order_amount', 'operator' => '>=', 'value' => 100],
                ['field' => 'user_registration_date', 'operator' => '>=', 'value' => '2024-01-01'],
                ['field' => 'country', 'operator' => 'in', 'value' => ['LT', 'LV', 'EE']],
            ], $this->faker->numberBetween(1, 2)),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'target_audience' => $this->faker->randomElement(['new_users', 'existing_users', 'vip_users']),
                'marketing_channel' => $this->faker->randomElement(['email', 'social', 'paid_ads']),
                'budget' => $this->faker->numberBetween(1000, 50000),
            ]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function withReward(float $amount, string $type = 'fixed'): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_amount' => $amount,
            'reward_type' => $type,
        ]);
    }

    public function withLimits(int $perUser, int $total): static
    {
        return $this->state(fn (array $attributes) => [
            'max_referrals_per_user' => $perUser,
            'max_total_referrals' => $total,
        ]);
    }
}

