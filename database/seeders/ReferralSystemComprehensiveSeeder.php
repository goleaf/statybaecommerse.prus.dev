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

final class ReferralSystemComprehensiveSeeder extends BaseSeeder
{
    /**
     * Lithuanian-first campaign catalog used by referral seeders.
     *
     * @var array<int, array<string, mixed>>
     */
    private const LITHUANIAN_CAMPAIGNS = [
        [
            'name'        => ['lt' => 'Pavasario rekomendacijų banga', 'en' => 'Spring Referral Wave'],
            'description' => [
                'lt' => 'Pakvieskite draugus ir gaukite papildomą nuolaidą statybų medžiagoms.',
                'en' => 'Invite friends and get an extra discount on construction materials.',
            ],
            'reward_type'   => 'fixed',
            'reward_amount' => 15.00,
        ],
        [
            'name'        => ['lt' => 'Meistrų klubo rekomendacijos', 'en' => 'Craftsmen Club Referrals'],
            'description' => [
                'lt' => 'Skirta profesionalams, kurie rekomenduoja mūsų platformą partneriams.',
                'en' => 'Designed for professionals referring our platform to partners.',
            ],
            'reward_type'   => 'percentage',
            'reward_amount' => 10.00,
        ],
        [
            'name'        => ['lt' => 'Naujakurių programa', 'en' => 'New Homeowner Program'],
            'description' => [
                'lt' => 'Naujų klientų pritraukimas su specialiomis premijomis pirmam užsakymui.',
                'en' => 'Attract new customers with special bonuses for the first order.',
            ],
            'reward_type'   => 'fixed',
            'reward_amount' => 20.00,
        ],
        [
            'name'        => ['lt' => 'Vasaros partnerių pasiūlymas', 'en' => 'Summer Partner Offer'],
            'description' => [
                'lt' => 'Partneriams, kurie aktyviai dalijasi rekomendacijų kodais savo kanaluose.',
                'en' => 'For partners actively sharing referral codes in their channels.',
            ],
            'reward_type'   => 'points',
            'reward_amount' => 25.00,
        ],
        [
            'name'        => ['lt' => 'Lojalių klientų programa', 'en' => 'Loyal Customer Program'],
            'description' => [
                'lt' => 'Papildomi atlygiai klientams, kurie sėkmingai kviečia naujus pirkėjus.',
                'en' => 'Additional rewards for customers who successfully invite new buyers.',
            ],
            'reward_type'   => 'fixed',
            'reward_amount' => 12.50,
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $campaigns = $this->createReferralCampaigns();
            $users = User::factory()->count(30)->create();
            $referralTargetCount = 60;

            $codes = $this->createReferralCodes($users, $campaigns, $referralTargetCount);
            $referrals = $this->createReferrals($codes, $referralTargetCount);
            $rewards = $this->createReferralRewards($referrals);
            $this->createReferralCodeStatistics($codes);
            $this->createReferralCodeUsageLogs($codes, $users);
            $this->createReferralRewardLogs($rewards);
            $this->createReferralStatistics($users);
        });
    }

    private function createReferralCampaigns(): Collection
    {
        return collect(self::LITHUANIAN_CAMPAIGNS)
            ->map(static function (array $campaign): ReferralCampaign {
                return ReferralCampaign::query()->create([
                    'name'                   => $campaign['name'],
                    'description'            => $campaign['description'],
                    'is_active'              => true,
                    'start_date'             => now()->subDays(random_int(5, 40)),
                    'end_date'               => now()->addDays(random_int(20, 120)),
                    'reward_amount'          => $campaign['reward_amount'],
                    'reward_type'            => $campaign['reward_type'],
                    'max_referrals_per_user' => random_int(2, 15),
                    'max_total_referrals'    => random_int(200, 3000),
                    'conditions'             => [
                        'minimum_order_amount' => random_int(40, 200),
                        'country'              => 'LT',
                    ],
                    'metadata' => [
                        'kanalas' => fake()->randomElement(['naujienlaiškis', 'partneriai', 'socialiniai_tinklai']),
                        'rinka'   => 'Lietuva',
                    ],
                ]);
            });
    }

    private function createReferralCodes(Collection $users, Collection $campaigns, int $desiredCount): Collection
    {
        return ReferralCode::factory()
            ->count($desiredCount)
            ->state(function () use ($users, $campaigns): array {
                $campaign = $campaigns->random();

                return [
                    'user_id'     => $users->random()->id,
                    'campaign_id' => $campaign->id,
                    'source'      => fake()->randomElement(['naujienlaiskis', 'partneriu_portalas', 'facebook_lt']),
                    'title'       => [
                        'lt' => 'Rekomendacijos kodas: ' . (string) $campaign->getTranslation('name', 'lt'),
                        'en' => 'Referral code: ' . (string) $campaign->getTranslation('name', 'en'),
                    ],
                    'description' => [
                        'lt' => 'Pritaikykite kodą pirkiniui ir gaukite atlygį Lietuvoje.',
                        'en' => 'Apply this code on checkout to earn a reward in Lithuania.',
                    ],
                    'reward_type'   => fake()->randomElement(['fixed', 'percentage']),
                    'reward_amount' => fake()->randomFloat(2, 5, 30),
                    'usage_limit'   => random_int(5, 40),
                    'usage_count'   => random_int(0, 4),
                    'tags'          => ['regionas' => 'LT', 'kanalas' => 'partneriai'],
                ];
            })
            ->create();
    }

    private function createReferrals(Collection $codes, int $desiredCount): Collection
    {
        $availableCodes = $codes->shuffle()->take($desiredCount)->values();

        if ($availableCodes->count() < $desiredCount) {
            throw new RuntimeException('Not enough referral codes to generate unique referrals.');
        }

        $referralSequences = $availableCodes->map(function (ReferralCode $code): array {
            $referred = User::factory()->create([
                'preferred_locale' => 'lt',
            ]);
            $campaignName = (string) optional($code->campaign)->getTranslation('name', 'lt');

            return [
                'referrer_id'   => $code->user_id,
                'referred_id'   => $referred->id,
                'referral_code' => $code->code,
                'source'        => fake()->randomElement(['el_pastas', 'partneriu_tinklas', 'tiesiogine_nuoroda']),
                'campaign'      => $campaignName !== '' ? $campaignName : 'Bendra LT kampanija',
                'status'        => fake()->randomElement(['pending', 'completed', 'expired']),
                'title'         => [
                    'lt' => 'Kliento rekomendacija',
                    'en' => 'Customer referral',
                ],
                'description' => [
                    'lt' => 'Naujas klientas pakviestas per rekomendacijos kodą.',
                    'en' => 'A new customer joined via a referral code.',
                ],
                'terms_conditions' => [
                    'lt' => 'Atlygis taikomas tik sėkmingam užsakymui Lietuvoje.',
                    'en' => 'Reward is applied only for successful orders in Lithuania.',
                ],
                'benefits_description' => [
                    'lt' => 'Rekomenduotojas ir pakviestas klientas gauna naudą.',
                    'en' => 'Both referrer and invited customer receive benefits.',
                ],
                'how_it_works' => [
                    'lt' => 'Pasidalinkite kodu, pakviestas klientas apsiperka, atlygis pritaikomas automatiškai.',
                    'en' => 'Share the code, invited customer purchases, reward is applied automatically.',
                ],
            ];
        });

        return Referral::factory()
            ->count($referralSequences->count())
            ->sequence(...$referralSequences->all())
            ->create();
    }

    private function createReferralRewards(Collection $referrals): Collection
    {
        return $referrals->flatMap(static function (Referral $referral): array {
            return [
                ReferralReward::factory()
                    ->referrerBonus()
                    ->state([
                        'referral_id' => $referral->id,
                        'user_id'     => $referral->referrer_id,
                        'status'      => fake()->randomElement(['pending', 'applied']),
                        'title'       => [
                            'lt' => 'Rekomenduotojo premija',
                            'en' => 'Referrer bonus',
                        ],
                        'description' => [
                            'lt' => 'Premija už sėkmingą kliento rekomendaciją.',
                            'en' => 'Bonus for a successful customer referral.',
                        ],
                        'metadata' => [
                            'kanalas' => 'rekomendacijos_sistema',
                            'rinka'   => 'LT',
                        ],
                    ])
                    ->create(),
                ReferralReward::factory()
                    ->referredDiscount()
                    ->state([
                        'referral_id' => $referral->id,
                        'user_id'     => $referral->referred_id,
                        'status'      => fake()->randomElement(['pending', 'applied', 'expired']),
                        'title'       => [
                            'lt' => 'Pakviesto kliento nuolaida',
                            'en' => 'Invited customer discount',
                        ],
                        'description' => [
                            'lt' => 'Nuolaida pirmajam pakviesto kliento užsakymui.',
                            'en' => 'Discount for the invited customer first order.',
                        ],
                        'metadata' => [
                            'kanalas' => 'rekomendacijos_sistema',
                            'rinka'   => 'LT',
                        ],
                    ])
                    ->create(),
            ];
        })->values();
    }

    private function createReferralCodeStatistics(Collection $referralCodes): void
    {
        foreach ($referralCodes as $referralCode) {
            for ($i = 0; $i < 14; $i++) {
                $date = now()->subDays($i)->toDateString();
                $views = random_int(30, 500);
                $clicks = random_int(10, $views);
                $signups = random_int(2, $clicks);
                $conversions = random_int(1, $signups);

                ReferralCodeStatistics::create([
                    'referral_code_id'  => $referralCode->id,
                    'date'              => $date,
                    'total_views'       => $views,
                    'total_clicks'      => $clicks,
                    'total_signups'     => $signups,
                    'total_conversions' => $conversions,
                    'total_revenue'     => random_int(20, 150) * $conversions,
                    'metadata'          => [
                        'source'       => 'lt-seeder',
                        'kanalas'      => fake()->randomElement(['facebook_lt', 'partneriu_portalas', 'el_pastas']),
                        'generated_at' => now()->toDateTimeString(),
                    ],
                ]);
            }
        }
    }

    private function createReferralCodeUsageLogs(Collection $referralCodes, Collection $users): void
    {
        foreach ($referralCodes as $referralCode) {
            for ($i = 0; $i < random_int(3, 10); $i++) {
                ReferralCodeUsageLog::create([
                    'referral_code_id' => $referralCode->id,
                    'user_id'          => $users->random()->id,
                    'ip_address'       => fake()->ipv4(),
                    'user_agent'       => fake()->userAgent(),
                    'referrer'         => fake()->randomElement([
                        'https://partneriai.lt/rekomendacijos',
                        'https://naujienlaiskis.lt/akcija',
                        'https://social.lt/pasiulymai',
                    ]),
                    'metadata' => [
                        'source'    => 'lt-seeder',
                        'miestas'   => fake()->randomElement(['Vilnius', 'Kaunas', 'Klaipėda']),
                        'timestamp' => now()->subDays(random_int(1, 21))->toDateTimeString(),
                    ],
                ]);
            }
        }
    }

    private function createReferralRewardLogs(Collection $referralRewards): void
    {
        foreach ($referralRewards as $referralReward) {
            $actions = ReferralRewardLog::ACTIONS;

            for ($i = 0; $i < random_int(1, 3); $i++) {
                ReferralRewardLog::create([
                    'referral_reward_id' => $referralReward->id,
                    'user_id'            => $referralReward->user_id,
                    'action'             => $actions[array_rand($actions)],
                    'data'               => [
                        'reward_amount' => $referralReward->amount,
                        'reward_type'   => $referralReward->type,
                        'priezastis'    => fake()->randomElement(['sekminga_rekomendacija', 'nuolaidos_pritaikymas', 'galiojimo_pabaiga']),
                        'timestamp'     => now()->subDays(random_int(1, 30))->toDateTimeString(),
                    ],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                ]);
            }
        }
    }

    private function createReferralStatistics(Collection $users): void
    {
        $users->each(static function (User $user): void {
            for ($dayOffset = 0; $dayOffset < 14; $dayOffset++) {
                $total = random_int(0, 12);
                $completed = random_int(0, $total);
                $pending = max(0, $total - $completed);

                ReferralStatistics::create([
                    'user_id'               => $user->id,
                    'date'                  => now()->subDays($dayOffset)->toDateString(),
                    'total_referrals'       => $total,
                    'completed_referrals'   => $completed,
                    'pending_referrals'     => $pending,
                    'total_rewards_earned'  => random_int(0, 80) * max(1, $completed),
                    'total_discounts_given' => random_int(0, 40) * max(1, $completed),
                    'metadata'              => [
                        'rinka'   => 'LT',
                        'segment' => fake()->randomElement(['statybininkai', 'naujakuriai', 'partneriai']),
                    ],
                ]);
            }
        });
    }
}
