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
            $referralTargetCount = 25;

            $codes = $this->createSampleReferralCodes($users, $referralTargetCount);
            $referrals = $this->createSampleReferrals($codes, $referralTargetCount);
            $this->createSampleRewards($referrals);
            $this->createSampleStatistics($users);
        });
    }

    private function createSampleReferralCodes($users, int $desiredCount)
    {
        return ReferralCode::factory()
            ->count(max($desiredCount, 20))
            ->state(fn () => [
                'user_id' => $users->random()->id,
            ])
            ->create();
    }

    private function createSampleReferrals($codes, int $desiredCount)
    {
        $availableCodes = $codes->shuffle()->take($desiredCount)->values();

        if ($availableCodes->count() < $desiredCount) {
            throw new \RuntimeException('Not enough referral codes to generate unique sample referrals.');
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
            collect(range(0, 29))->each(function (int $dayOffset) use ($user): void {
                ReferralStatistics::factory()
                    ->state([
                        'user_id' => $user->id,
                        'date'    => now()->subDays($dayOffset)->toDateString(),
                    ])
                    ->create();
            });
        });
    }
}
