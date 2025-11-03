<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ReferralSystemComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $campaigns = $this->createReferralCampaigns();
            $users = User::factory()->count(30)->create();
            $referralTargetCount = 60;

            $codes = $this->createReferralCodes($users, $campaigns, $referralTargetCount);
            $referrals = $this->createReferrals($codes, $referralTargetCount);
            $rewards = $this->createReferralRewards($referrals);
            $this->createReferralCodeStatistics();
            $this->createReferralCodeUsageLogs();
            $this->createReferralRewardLogs();
        });
    }

    private function createReferralCampaigns(): \Illuminate\Support\Collection
    {
        return ReferralCampaign::factory()
            ->count(5)
            ->create();
    }

    private function createReferralCodes($users, $campaigns, int $desiredCount): \Illuminate\Support\Collection
    {
        return ReferralCode::factory()
            ->count($desiredCount)
            ->state(function () use ($users, $campaigns) {
                return [
                    'user_id'     => $users->random()->id,
                    'campaign_id' => $campaigns->random()->id,
                ];
            })
            ->create();
    }

    private function createReferrals($codes, int $desiredCount): \Illuminate\Support\Collection
    {
        $availableCodes = $codes->shuffle()->take($desiredCount)->values();

        if ($availableCodes->count() < $desiredCount) {
            throw new \RuntimeException('Not enough referral codes to generate unique referrals.');
        }

        $referralSequences = $availableCodes->map(function (ReferralCode $code): array {
            $referred = User::factory()->create();

            return [
                'referrer_id'   => $code->user_id,
                'referred_id'   => $referred->id,
                'referral_code' => $code->code,
            ];
        });

        return Referral::factory()
            ->count($referralSequences->count())
            ->sequence(...$referralSequences->all())
            ->create();
    }

    private function createReferralRewards($referrals): \Illuminate\Support\Collection
    {
        return ReferralReward::factory()
            ->count(120)
            ->state(function () use ($referrals) {
                $referral = $referrals->random();

                return [
                    'referral_id' => $referral->id,
                    'user_id'     => fake()->boolean(70) ? $referral->referrer_id : $referral->referred_id,
                ];
            })
            ->create();
    }

    private function createReferralCodeStatistics(): void
    {
        $referralCodes = ReferralCode::all();

        foreach ($referralCodes as $referralCode) {
            // Create statistics for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->toDateString();

                ReferralCodeStatistics::create([
                    'referral_code_id'  => $referralCode->id,
                    'date'              => $date,
                    'total_views'       => rand(0, 100),
                    'total_clicks'      => rand(0, 50),
                    'total_signups'     => rand(0, 20),
                    'total_conversions' => rand(0, 10),
                    'total_revenue'     => rand(0, 1000),
                    'metadata'          => [
                        'source'       => 'system',
                        'generated_at' => now(),
                    ],
                ]);
            }
        }
    }

    private function createReferralCodeUsageLogs(): void
    {
        $referralCodes = ReferralCode::all();
        $users = User::all();

        foreach ($referralCodes as $referralCode) {
            // Create usage logs for each referral code
            for ($i = 0; $i < rand(5, 20); $i++) {
                ReferralCodeUsageLog::create([
                    'referral_code_id' => $referralCode->id,
                    'user_id'          => $users->random()->id,
                    'ip_address'       => fake()->ipv4(),
                    'user_agent'       => fake()->userAgent(),
                    'referrer'         => fake()->url(),
                    'metadata'         => [
                        'source'    => 'system',
                        'timestamp' => now()->subDays(rand(1, 30)),
                    ],
                ]);
            }
        }
    }

    private function createReferralRewardLogs(): void
    {
        $referralRewards = ReferralReward::all();

        foreach ($referralRewards as $referralReward) {
            $actions = ReferralRewardLog::ACTIONS;

            // Create logs for each reward
            for ($i = 0; $i < rand(1, 5); $i++) {
                ReferralRewardLog::create([
                    'referral_reward_id' => $referralReward->id,
                    'user_id'            => $referralReward->user_id,
                    'action'             => $actions[rand(0, 3)],
                    'data'               => [
                        'reward_amount' => $referralReward->amount,
                        'reward_type'   => $referralReward->type,
                        'timestamp'     => now()->subDays(rand(1, 30)),
                    ],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                ]);
            }
        }
    }
}
