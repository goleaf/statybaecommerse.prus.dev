<?php declare(strict_types=1);

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
        // Create some users for referrals
        $users = User::factory()->count(20)->create();

        // Create referral codes for some users
        $users->take(10)->each(function (User $user) {
            ReferralCode::factory()->active()->create(['user_id' => $user->id]);
        });

        // Create referrals between users
        $referrals = collect();
        
        for ($i = 0; $i < 50; $i++) {
            $referrer = $users->random();
            $referred = $users->where('id', '!=', $referrer->id)->random();
            
            // Check if this combination already exists
            if ($referrals->contains(fn($r) => $r->referrer_id === $referrer->id && $r->referred_id === $referred->id)) {
                continue;
            }
            
            $referral = Referral::factory()->create([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
            ]);
            
            $referrals->push($referral);
        }

        // Create rewards for some referrals
        $referrals->random(30)->each(function (Referral $referral) {
            // Create referrer bonus
            ReferralReward::factory()->referrerBonus()->create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referrer_id,
            ]);

            // Create referred discount (50% chance)
            if (fake()->boolean(50)) {
                ReferralReward::factory()->referredDiscount()->create([
                    'referral_id' => $referral->id,
                    'user_id' => $referral->referred_id,
                ]);
            }
        });

        // Create some completed referrals with applied rewards
        $referrals->random(15)->each(function (Referral $referral) {
            $referral->update(['status' => 'completed', 'completed_at' => now()]);
            
            // Apply some rewards
            $referral->rewards()->take(1)->each(function ($reward) {
                $reward->update(['status' => 'applied', 'applied_at' => now()]);
            });
        });

        // Create some expired referrals
        $referrals->random(5)->each(function (Referral $referral) {
            $referral->update(['status' => 'expired']);
        });

        // Create some expired rewards
        ReferralReward::factory()->count(10)->expired()->create();

        $this->command->info('Referral system seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . $users->count() . ' users');
        $this->command->info('- ' . $referrals->count() . ' referrals');
        $this->command->info('- ' . ReferralCode::count() . ' referral codes');
        $this->command->info('- ' . ReferralReward::count() . ' referral rewards');
    }
}
