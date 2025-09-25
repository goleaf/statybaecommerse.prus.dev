<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ReferralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()
            ->count(20)
            ->has(ReferralCode::factory()->count(1)->active(), 'referralCodes')
            ->create();

        $referrals = Referral::factory()
            ->count(50)
            ->state(function () use ($users) {
                $referrer = $users->random();
                $referred = $users->where('id', '!=', $referrer->id)->random();

                return [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referred->id,
                ];
            })
            ->create();

        $referrals->each(function (Referral $referral): void {
            ReferralReward::factory()
                ->count(fake()->numberBetween(1, 2))
                ->referrerBonus()
                ->for($referral, 'referral')
                ->for($referral->referrer, 'user')
                ->create();

            if (fake()->boolean(50)) {
                ReferralReward::factory()
                    ->referredDiscount()
                    ->for($referral, 'referral')
                    ->for($referral->referred, 'user')
                    ->create();
            }
        });

        $referrals->random(15)->each(function (Referral $referral): void {
            $referral->update(['status' => 'completed', 'completed_at' => now()]);

            $referral->rewards()->take(1)->each(function (ReferralReward $reward): void {
                $reward->update(['status' => 'applied', 'applied_at' => now()]);
            });
        });

        $referrals->random(5)->each(function (Referral $referral): void {
            $referral->update(['status' => 'expired']);
        });

        ReferralReward::factory()
            ->count(10)
            ->expired()
            ->create();

        $this->command->info('Referral system seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- '.$users->count().' users');
        $this->command->info('- '.$referrals->count().' referrals');
        $this->command->info('- '.ReferralCode::count().' referral codes');
        $this->command->info('- '.ReferralReward::count().' referral rewards');
    }
}
