<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralRewardLog>
 */
final class ReferralRewardLogFactory extends Factory
{
    protected $model = ReferralRewardLog::class;

    public function definition(): array
    {
        return [
            'referral_reward_id' => ReferralReward::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(ReferralRewardLog::ACTIONS),
            'data' => [
                'amount' => $this->faker->randomFloat(2, 1, 100),
                'currency' => 'EUR',
                'reward_type' => $this->faker->randomElement(['discount', 'credit', 'points']),
            ],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    public function earned(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ReferralRewardLog::ACTION_EARNED,
            'data' => [
                'amount' => $this->faker->randomFloat(2, 1, 100),
                'currency' => 'EUR',
                'reward_type' => 'discount',
                'earned_at' => now()->toISOString(),
            ],
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ReferralRewardLog::ACTION_EXPIRED,
            'data' => [
                'amount' => $this->faker->randomFloat(2, 1, 100),
                'currency' => 'EUR',
                'reward_type' => 'credit',
                'expired_at' => now()->toISOString(),
            ],
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ReferralRewardLog::ACTION_CANCELLED,
            'data' => [
                'amount' => $this->faker->randomFloat(2, 1, 100),
                'currency' => 'EUR',
                'reward_type' => 'points',
                'cancelled_at' => now()->toISOString(),
                'reason' => $this->faker->sentence(),
            ],
        ]);
    }

    public function redeemed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ReferralRewardLog::ACTION_REDEEMED,
            'data' => [
                'amount' => $this->faker->randomFloat(2, 1, 100),
                'currency' => 'EUR',
                'reward_type' => 'discount',
                'redeemed_at' => now()->toISOString(),
            ],
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
