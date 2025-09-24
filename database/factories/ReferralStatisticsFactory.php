<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ReferralStatistics>
 */
final class ReferralStatisticsFactory extends Factory
{
    protected $model = ReferralStatistics::class;

    public function definition(): array
    {
        $totalReferrals = fake()->numberBetween(0, 20);
        $completedReferrals = fake()->numberBetween(0, $totalReferrals);
        $pendingReferrals = max(0, $totalReferrals - $completedReferrals);

        return [
            'user_id' => User::factory(),
            'date' => now()->toDateString(),
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_rewards_earned' => fake()->randomFloat(2, 0, 1000),
            'total_discounts_given' => fake()->randomFloat(2, 0, 1000),
            'metadata' => [],
        ];
    }
}
