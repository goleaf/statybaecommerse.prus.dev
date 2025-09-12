<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\ReferralStatistics;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ReferralSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->createSampleUsers();
            $this->createSampleReferralCodes();
            $this->createSampleReferrals();
            $this->createSampleRewards();
            $this->createSampleStatistics();
        });
    }

    private function createSampleUsers(): void
    {
        // Create some sample users if they don't exist
        if (User::count() < 10) {
            User::factory(10)->create();
        }
    }

    private function createSampleReferralCodes(): void
    {
        $users = User::limit(5)->get();

        foreach ($users as $user) {
            if (! $user->hasActiveReferralCode()) {
                ReferralCode::factory()->active()->forUser($user)->create();
            }
        }
    }

    private function createSampleReferrals(): void
    {
        $users = User::all();
        $referralService = app(ReferralService::class);

        // Create some sample referrals
        for ($i = 0; $i < 15; $i++) {
            $referrer = $users->random();
            $referred = $users->where('id', '!=', $referrer->id)->random();

            // Check if this combination already exists
            if (! Referral::where('referrer_id', $referrer->id)
                ->where('referred_id', $referred->id)
                ->exists()) {

                $referral = Referral::factory()->create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referred->id,
                ]);

                // Randomly complete some referrals
                if (rand(1, 3) === 1) {
                    $referral->markAsCompleted();
                }
            }
        }
    }

    private function createSampleRewards(): void
    {
        $referrals = Referral::with(['referrer', 'referred'])->get();

        foreach ($referrals as $referral) {
            if ($referral->status === 'completed') {
                // Create referred discount
                ReferralReward::factory()->referredDiscount()
                    ->forReferral($referral)
                    ->forUser($referral->referred)
                    ->applied()
                    ->create([
                        'amount' => 5.0,
                    ]);

                // Randomly create referrer bonus
                if (rand(1, 2) === 1) {
                    ReferralReward::factory()->referrerBonus()
                        ->forReferral($referral)
                        ->forUser($referral->referrer)
                        ->pending()
                        ->create([
                            'amount' => rand(5, 25),
                        ]);
                }
            } else {
                // Create pending rewards for incomplete referrals
                if (rand(1, 3) === 1) {
                    ReferralReward::factory()->referredDiscount()
                        ->forReferral($referral)
                        ->forUser($referral->referred)
                        ->pending()
                        ->create([
                            'amount' => 5.0,
                        ]);
                }
            }
        }
    }

    private function createSampleStatistics(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create statistics for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->toDateString();

                $stats = ReferralStatistics::getOrCreateForUserAndDate($user->id, $date);

                // Randomly update statistics
                if (rand(1, 5) === 1) {
                    $stats->update([
                        'total_referrals' => rand(0, 5),
                        'completed_referrals' => rand(0, 3),
                        'pending_referrals' => rand(0, 2),
                        'total_rewards_earned' => rand(0, 100),
                        'total_discounts_given' => rand(0, 50),
                    ]);
                }
            }
        }
    }
}
