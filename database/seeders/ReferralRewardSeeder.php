<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ReferralReward;
use App\Models\Referral;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Seeder;

final class ReferralRewardSeeder extends Seeder
{
    public function run(): void
    {
        // Create some sample referral rewards
        $users = User::limit(10)->get();
        $referrals = Referral::limit(20)->get();
        $orders = Order::limit(15)->get();

        if ($users->isEmpty() || $referrals->isEmpty()) {
            $this->command->warn('No users or referrals found. Please run UserSeeder and ReferralSeeder first.');
            return;
        }

        // Create referrer bonuses
        ReferralReward::factory()
            ->count(15)
            ->referrerBonus()
            ->state(function () use ($users, $referrals) {
                return [
                    'user_id' => $users->random()->id,
                    'referral_id' => $referrals->random()->id,
                    'title' => [
                        'en' => 'Referrer Bonus',
                        'lt' => 'Referralo bonusas',
                    ],
                    'description' => [
                        'en' => 'Bonus for successfully referring a new customer',
                        'lt' => 'Bonusas už sėkmingai referraluotą naują klientą',
                    ],
                ];
            })
            ->create();

        // Create referred discounts
        ReferralReward::factory()
            ->count(20)
            ->referredDiscount()
            ->state(function () use ($users, $referrals, $orders) {
                return [
                    'user_id' => $users->random()->id,
                    'referral_id' => $referrals->random()->id,
                    'order_id' => $orders->random()->id,
                    'title' => [
                        'en' => 'Welcome Discount',
                        'lt' => 'Sveikinimo nuolaida',
                    ],
                    'description' => [
                        'en' => 'Special discount for new customers',
                        'lt' => 'Speciali nuolaida naujiems klientams',
                    ],
                ];
            })
            ->create();

        // Create some pending rewards
        ReferralReward::factory()
            ->count(10)
            ->pending()
            ->state(function () use ($users, $referrals) {
                return [
                    'user_id' => $users->random()->id,
                    'referral_id' => $referrals->random()->id,
                    'title' => [
                        'en' => 'Pending Reward',
                        'lt' => 'Laukiantis atlygis',
                    ],
                    'description' => [
                        'en' => 'Reward waiting to be applied',
                        'lt' => 'Atlygis laukiantis pritaikymo',
                    ],
                ];
            })
            ->create();

        // Create some applied rewards
        ReferralReward::factory()
            ->count(12)
            ->applied()
            ->state(function () use ($users, $referrals, $orders) {
                return [
                    'user_id' => $users->random()->id,
                    'referral_id' => $referrals->random()->id,
                    'order_id' => $orders->random()->id,
                    'title' => [
                        'en' => 'Applied Reward',
                        'lt' => 'Pritaikytas atlygis',
                    ],
                    'description' => [
                        'en' => 'Reward that has been successfully applied',
                        'lt' => 'Atlygis, kuris buvo sėkmingai pritaikytas',
                    ],
                ];
            })
            ->create();

        // Create some expired rewards
        ReferralReward::factory()
            ->count(8)
            ->expired()
            ->state(function () use ($users, $referrals) {
                return [
                    'user_id' => $users->random()->id,
                    'referral_id' => $referrals->random()->id,
                    'title' => [
                        'en' => 'Expired Reward',
                        'lt' => 'Pasibaigęs atlygis',
                    ],
                    'description' => [
                        'en' => 'Reward that has expired',
                        'lt' => 'Atlygis, kuris pasibaigė',
                    ],
                ];
            })
            ->create();

        $this->command->info('ReferralRewardSeeder completed successfully!');
    }
}
