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
        DB::transaction(function () {
            $this->createReferralCampaigns();
            $this->createReferralCodes();
            $this->createReferrals();
            $this->createReferralRewards();
            $this->createReferralCodeStatistics();
            $this->createReferralCodeUsageLogs();
            $this->createReferralRewardLogs();
        });
    }

    private function createReferralCampaigns(): void
    {
        $campaigns = [
            [
                'name' => [
                    'en' => 'Welcome Bonus Campaign',
                    'lt' => 'Sveikinimo bonusas kampanija',
                ],
                'description' => [
                    'en' => 'Get bonus rewards for referring new users',
                    'lt' => 'Gaukite bonusų atlygius už naujų naudotojų rekomendavimą',
                ],
                'is_active' => true,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(30),
                'reward_amount' => 10.00,
                'reward_type' => 'discount',
                'max_referrals_per_user' => 5,
                'max_total_referrals' => 1000,
                'conditions' => [
                    'min_order_value' => 50.00,
                    'new_user_only' => true,
                ],
                'metadata' => [
                    'campaign_type' => 'referral',
                    'target_audience' => 'new_users',
                ],
            ],
            [
                'name' => [
                    'en' => 'Holiday Special Campaign',
                    'lt' => 'Šventinė speciali kampanija',
                ],
                'description' => [
                    'en' => 'Special holiday referral campaign with increased rewards',
                    'lt' => 'Speciali šventinė rekomendacijų kampanija su padidintais atlygiais',
                ],
                'is_active' => true,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(15),
                'reward_amount' => 25.00,
                'reward_type' => 'credit',
                'max_referrals_per_user' => 10,
                'max_total_referrals' => 500,
                'conditions' => [
                    'min_order_value' => 100.00,
                    'new_user_only' => true,
                ],
                'metadata' => [
                    'campaign_type' => 'holiday',
                    'target_audience' => 'all_users',
                ],
            ],
        ];

        foreach ($campaigns as $campaignData) {
            ReferralCampaign::create($campaignData);
        }
    }

    private function createReferralCodes(): void
    {
        $users = User::limit(10)->get();
        $campaigns = ReferralCampaign::all();

        foreach ($users as $user) {
            $campaign = $campaigns->random();

            ReferralCode::create([
                'user_id' => $user->id,
                'code' => strtoupper(substr(md5(uniqid()), 0, 8)),
                'title' => [
                    'en' => 'Referral Code for '.$user->name,
                    'lt' => 'Rekomendacijų kodas '.$user->name,
                ],
                'description' => [
                    'en' => 'Use this code to get special rewards',
                    'lt' => 'Naudokite šį kodą, kad gautumėte specialius atlygius',
                ],
                'is_active' => true,
                'expires_at' => now()->addDays(90),
                'usage_limit' => rand(5, 20),
                'usage_count' => 0,
                'reward_amount' => rand(5, 50),
                'reward_type' => ['discount', 'credit', 'points', 'gift'][rand(0, 3)],
                'campaign_id' => $campaign->id,
                'source' => ['website', 'email', 'social', 'mobile'][rand(0, 3)],
                'conditions' => [
                    'min_order_value' => rand(25, 100),
                    'new_user_only' => true,
                ],
                'tags' => [
                    'premium' => 'true',
                    'category' => 'referral',
                ],
                'metadata' => [
                    'created_by' => 'system',
                    'priority' => rand(1, 5),
                ],
            ]);
        }
    }

    private function createReferrals(): void
    {
        $users = User::all();
        $referralCodes = ReferralCode::all();

        for ($i = 0; $i < 50; $i++) {
            $referrer = $users->random();
            $referred = $users->where('id', '!=', $referrer->id)->random();
            $referralCode = $referralCodes->random();

            // Check if this combination already exists
            if (! Referral::where('referrer_id', $referrer->id)
                ->where('referred_id', $referred->id)
                ->exists()) {

                $statuses = ['pending', 'active', 'completed', 'expired', 'cancelled'];
                $status = $statuses[rand(0, 4)];

                $referral = Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referred->id,
                    'referral_code' => $referralCode->code,
                    'status' => $status,
                    'completed_at' => $status === 'completed' ? now()->subDays(rand(1, 30)) : null,
                    'expires_at' => now()->addDays(rand(1, 90)),
                    'source' => ['website', 'email', 'social', 'mobile'][rand(0, 3)],
                    'campaign' => $referralCode->campaign_id,
                    'utm_source' => ['google', 'facebook', 'twitter', 'email'][rand(0, 3)],
                    'utm_medium' => ['cpc', 'social', 'email', 'organic'][rand(0, 3)],
                    'utm_campaign' => 'referral_campaign_'.rand(1, 5),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'title' => [
                        'en' => 'Referral from '.$referrer->name,
                        'lt' => 'Rekomendacija nuo '.$referrer->name,
                    ],
                    'description' => [
                        'en' => 'Get special rewards when you sign up using this referral',
                        'lt' => 'Gaukite specialius atlygius, kai užsiregistruojate naudodami šią rekomendaciją',
                    ],
                    'terms_conditions' => [
                        'en' => 'Terms and conditions apply. See website for details.',
                        'lt' => 'Taikomos sąlygos. Detales žiūrėkite svetainėje.',
                    ],
                    'benefits_description' => [
                        'en' => 'Exclusive benefits for referred users',
                        'lt' => 'Ekskluzyvūs privalumai rekomenduotiems naudotojams',
                    ],
                    'how_it_works' => [
                        'en' => 'Simply sign up using the referral link and start earning rewards',
                        'lt' => 'Tiesiog užsiregistruokite naudodami rekomendacijos nuorodą ir pradėkite uždirbti atlygius',
                    ],
                    'seo_title' => [
                        'en' => 'Referral Program - Get Rewards',
                        'lt' => 'Rekomendacijų programa - gaukite atlygius',
                    ],
                    'seo_description' => [
                        'en' => 'Join our referral program and earn rewards for every successful referral',
                        'lt' => 'Prisijunkite prie mūsų rekomendacijų programos ir uždirbkite atlygius už kiekvieną sėkmingą rekomendaciją',
                    ],
                    'seo_keywords' => [
                        'referral' => 'program',
                        'rewards' => 'bonus',
                        'earn' => 'money',
                    ],
                    'metadata' => [
                        'created_by' => 'system',
                        'priority' => rand(1, 5),
                    ],
                ]);
            }
        }
    }

    private function createReferralRewards(): void
    {
        $referrals = Referral::where('status', 'completed')->get();

        foreach ($referrals as $referral) {
            // Create reward for referrer
            ReferralReward::create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referrer_id,
                'type' => 'referrer_bonus',
                'amount' => rand(10, 50),
                'currency_code' => 'EUR',
                'status' => 'applied',
                'applied_at' => now()->subDays(rand(1, 30)),
                'expires_at' => now()->addDays(90),
                'title' => [
                    'en' => 'Referrer Bonus',
                    'lt' => 'Rekomendavimo bonusas',
                ],
                'description' => [
                    'en' => 'Bonus for successful referral',
                    'lt' => 'Bonusas už sėkmingą rekomendaciją',
                ],
                'is_active' => true,
                'priority' => rand(1, 5),
                'conditions' => [
                    'min_order_value' => 50.00,
                ],
                'reward_data' => [
                    'referral_id' => $referral->id,
                    'bonus_type' => 'referrer',
                ],
                'metadata' => [
                    'created_by' => 'system',
                ],
            ]);

            // Create reward for referred user
            ReferralReward::create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referred_id,
                'type' => 'referred_discount',
                'amount' => rand(5, 25),
                'currency_code' => 'EUR',
                'status' => 'applied',
                'applied_at' => now()->subDays(rand(1, 30)),
                'expires_at' => now()->addDays(60),
                'title' => [
                    'en' => 'Welcome Discount',
                    'lt' => 'Sveikinimo nuolaida',
                ],
                'description' => [
                    'en' => 'Discount for new user',
                    'lt' => 'Nuolaida naujam naudotojui',
                ],
                'is_active' => true,
                'priority' => rand(1, 5),
                'conditions' => [
                    'min_order_value' => 25.00,
                ],
                'reward_data' => [
                    'referral_id' => $referral->id,
                    'bonus_type' => 'referred',
                ],
                'metadata' => [
                    'created_by' => 'system',
                ],
            ]);
        }
    }

    private function createReferralCodeStatistics(): void
    {
        $referralCodes = ReferralCode::all();

        foreach ($referralCodes as $referralCode) {
            // Create statistics for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->toDateString();

                ReferralCodeStatistics::create([
                    'referral_code_id' => $referralCode->id,
                    'date' => $date,
                    'total_views' => rand(0, 100),
                    'total_clicks' => rand(0, 50),
                    'total_signups' => rand(0, 20),
                    'total_conversions' => rand(0, 10),
                    'total_revenue' => rand(0, 1000),
                    'metadata' => [
                        'source' => 'system',
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
                    'user_id' => $users->random()->id,
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'referrer' => fake()->url(),
                    'metadata' => [
                        'source' => 'system',
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
            $actions = ['earned', 'redeemed', 'expired', 'cancelled'];

            // Create logs for each reward
            for ($i = 0; $i < rand(1, 5); $i++) {
                ReferralRewardLog::create([
                    'referral_reward_id' => $referralReward->id,
                    'user_id' => $referralReward->user_id,
                    'action' => $actions[rand(0, 3)],
                    'data' => [
                        'reward_amount' => $referralReward->amount,
                        'reward_type' => $referralReward->type,
                        'timestamp' => now()->subDays(rand(1, 30)),
                    ],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                ]);
            }
        }
    }
}
