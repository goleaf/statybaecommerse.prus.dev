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
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ReferralSystemSeeder extends BaseSeeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $users = User::factory()->count(15)->create();
            $referralTargetCount = 25;
            $campaigns = $this->createCampaigns();

            $codes = $this->createSampleReferralCodes($users, $campaigns, $referralTargetCount);
            $referrals = $this->createSampleReferrals($codes, $referralTargetCount);
            $rewards = $this->createSampleRewards($referrals);
            $this->createCodeStatistics($codes);
            $this->createCodeUsageLogs($codes, $users);
            $this->createRewardLogs($rewards);
            $this->createSampleStatistics($users);
        });
    }

    private function createCampaigns(): Collection
    {
        return collect([
            ['lt' => 'Rekomenduok ir sutaupyk', 'en' => 'Refer and save'],
            ['lt' => 'Partnerių savaitė', 'en' => 'Partner week'],
            ['lt' => 'Naujo kliento premija', 'en' => 'New customer bonus'],
        ])->map(static fn (array $name): ReferralCampaign => ReferralCampaign::query()->create([
            'name'        => $name,
            'description' => [
                'lt' => 'Lietuvos rinkai skirta rekomendacijų kampanija.',
                'en' => 'Referral campaign designed for the Lithuanian market.',
            ],
            'is_active'              => true,
            'start_date'             => now()->subDays(10),
            'end_date'               => now()->addDays(60),
            'reward_amount'          => random_int(10, 25),
            'reward_type'            => 'fixed',
            'max_referrals_per_user' => 10,
            'max_total_referrals'    => 1000,
            'conditions'             => ['country' => 'LT'],
            'metadata'               => ['rinka' => 'LT'],
        ]));
    }

    private function createSampleReferralCodes(Collection $users, Collection $campaigns, int $desiredCount): Collection
    {
        return ReferralCode::factory()
            ->count(max($desiredCount, 20))
            ->state(fn (): array => [
                'user_id'       => $users->random()->id,
                'campaign_id'   => $campaigns->random()->id,
                'source'        => fake()->randomElement(['naujienlaiskis', 'partneriu_tinklas']),
                'title'         => ['lt' => 'Rekomendacijos kodas', 'en' => 'Referral code'],
                'description'   => ['lt' => 'Kodas skirta Lietuvos klientams.', 'en' => 'Code for Lithuanian customers.'],
                'reward_type'   => 'fixed',
                'reward_amount' => random_int(5, 20),
                'metadata'      => ['regionas' => 'LT'],
            ])
            ->create();
    }

    private function createSampleReferrals(Collection $codes, int $desiredCount): Collection
    {
        $availableCodes = $codes->shuffle()->take($desiredCount)->values();

        if ($availableCodes->count() < $desiredCount) {
            throw new RuntimeException('Not enough referral codes to generate unique sample referrals.');
        }

        $referralSequences = $availableCodes->map(function (ReferralCode $code): array {
            $referred = User::factory()->create();

            return [
                'referrer_id'   => $code->user_id,
                'referred_id'   => $referred->id,
                'referral_code' => $code->code,
                'source'        => 'el_pastas',
                'campaign'      => 'Lietuvos rekomendacijų kampanija',
                'title'         => ['lt' => 'Sėkminga rekomendacija', 'en' => 'Successful referral'],
                'description'   => [
                    'lt' => 'Pakviestas naujas klientas naudojant rekomendacijos kodą.',
                    'en' => 'A new customer invited with a referral code.',
                ],
                'terms_conditions' => [
                    'lt' => 'Atlygis taikomas tik Lietuvoje atliktiems užsakymams.',
                    'en' => 'Reward is applied only for orders placed in Lithuania.',
                ],
            ];
        });

        return Referral::factory()
            ->count($referralSequences->count())
            ->sequence(...$referralSequences->all())
            ->create();
    }

    private function createSampleRewards(Collection $referrals): Collection
    {
        return $referrals->flatMap(static function (Referral $referral): array {
            return [
                ReferralReward::factory()
                    ->referrerBonus()
                    ->for($referral, 'referral')
                    ->for($referral->referrer, 'user')
                    ->state([
                        'title'       => ['lt' => 'Rekomenduotojo premija', 'en' => 'Referrer bonus'],
                        'description' => ['lt' => 'Premija už pakviestą klientą.', 'en' => 'Bonus for invited customer.'],
                    ])
                    ->create(),

                ReferralReward::factory()
                    ->referredDiscount()
                    ->for($referral, 'referral')
                    ->for($referral->referred, 'user')
                    ->state([
                        'title'       => ['lt' => 'Pakviesto kliento nuolaida', 'en' => 'Invited customer discount'],
                        'description' => ['lt' => 'Nuolaida pirmam užsakymui.', 'en' => 'Discount for first order.'],
                    ])
                    ->create(),
            ];
        })->values();
    }

    private function createCodeStatistics(Collection $codes): void
    {
        $codes->each(static function (ReferralCode $code): void {
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i)->toDateString();
                ReferralCodeStatistics::query()->create([
                    'referral_code_id'  => $code->id,
                    'date'              => $date,
                    'total_views'       => random_int(20, 250),
                    'total_clicks'      => random_int(10, 150),
                    'total_signups'     => random_int(5, 80),
                    'total_conversions' => random_int(1, 40),
                    'total_revenue'     => random_int(20, 400),
                    'metadata'          => ['rinka' => 'LT'],
                ]);
            }
        });
    }

    private function createCodeUsageLogs(Collection $codes, Collection $users): void
    {
        $codes->take(30)->each(static function (ReferralCode $code) use ($users): void {
            for ($i = 0; $i < random_int(1, 4); $i++) {
                ReferralCodeUsageLog::query()->create([
                    'referral_code_id' => $code->id,
                    'user_id'          => $users->random()->id,
                    'ip_address'       => fake()->ipv4(),
                    'user_agent'       => fake()->userAgent(),
                    'referrer'         => 'https://partneriai.lt/akcija',
                    'metadata'         => ['miestas' => fake()->randomElement(['Vilnius', 'Kaunas', 'Šiauliai'])],
                ]);
            }
        });
    }

    private function createRewardLogs(Collection $rewards): void
    {
        $rewards->each(static function (ReferralReward $reward): void {
            ReferralRewardLog::query()->create([
                'referral_reward_id' => $reward->id,
                'user_id'            => $reward->user_id,
                'action'             => fake()->randomElement(ReferralRewardLog::ACTIONS),
                'data'               => [
                    'priezastis' => 'rekomendacijos_premija',
                    'suma'       => $reward->amount,
                ],
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ]);
        });
    }

    private function createSampleStatistics(Collection $users): void
    {
        $users->each(function (User $user): void {
            collect(range(0, 29))->each(function (int $dayOffset) use ($user): void {
                ReferralStatistics::factory()
                    ->state([
                        'user_id'  => $user->id,
                        'date'     => now()->subDays($dayOffset)->toDateString(),
                        'metadata' => ['rinka' => 'LT'],
                    ])
                    ->create();
            });
        });
    }
}
