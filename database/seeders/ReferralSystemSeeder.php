<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ReferralSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $users = User::factory()->count(15)->create();
            $codes = $this->createSampleReferralCodes($users);
            $referrals = $this->createSampleReferrals($codes);
            $this->createSampleRewards($referrals);
            $this->createSampleStatistics($users);
        });
    }

    private function createSampleReferralCodes($users)
    {
        return ReferralCode::factory()
            ->count(20)
            ->state(fn () => [
                'user_id' => $users->random()->id,
            ])
            ->create();
    }

    private function createSampleReferrals($codes)
    {
        return Referral::factory()
            ->count(25)
            ->state(function () use ($codes) {
                $code = $codes->random();
                $referrer = $code->user;
                $referred = User::factory()->create();

                return [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referred->id,
                    'referral_code' => $code->code,
                ];
            })
            ->create();
    }

    private function createSampleRewards($referrals): void
    {
        $referrals->each(function (Referral $referral): void {
            ReferralReward::factory()
                ->referrerBonus()
                ->for($referral, 'referral')
                ->for($referral->referrer, 'user')
                ->create();

            ReferralReward::factory()
                ->referredDiscount()
                ->for($referral, 'referral')
                ->for($referral->referred, 'user')
                ->create();
        });
    }

    private function createSampleStatistics($users): void
    {
        $users->each(function (User $user): void {
            ReferralStatistics::factory()
                ->count(30)
                ->state(fn (array $attributes) => [
                    'user_id' => $user->id,
                    'date' => now()->subDays($attributes['total_referrals'] ?? 0)->toDateString(),
                ])
                ->create();
        });
    }
}
