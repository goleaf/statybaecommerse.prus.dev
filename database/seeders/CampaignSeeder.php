<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $campaigns = [
                [
                    'name' => 'Black Friday',
                    'status' => 'scheduled',
                    'starts_at' => now()->addMonth()->startOfMonth(),
                    'ends_at' => now()->addMonth()->endOfMonth(),
                    'is_featured' => true,
                ],
                [
                    'name' => 'Cyber Monday',
                    'status' => 'scheduled',
                    'starts_at' => now()->addMonths(1)->startOfMonth()->addDays(3),
                    'ends_at' => now()->addMonths(1)->startOfMonth()->addDays(3),
                ],
                [
                    'name' => 'Vasaros išpardavimas',
                    'status' => 'active',
                    'starts_at' => now()->subDays(5),
                    'ends_at' => now()->addDays(20),
                    'is_featured' => true,
                ],
            ];

            foreach ($campaigns as $c) {
                $slug = Str::slug($c['name']);
                $campaignId = DB::table('discount_campaigns')->where('slug', $slug)->value('id');

                if (! $campaignId) {
                    $campaignId = DB::table('discount_campaigns')->insertGetId([
                        'name' => $c['name'],
                        'slug' => $slug,
                        'starts_at' => $c['starts_at'] ?? null,
                        'ends_at' => $c['ends_at'] ?? null,
                        'channel_id' => null,
                        'zone_id' => null,
                        'status' => $c['status'] ?? 'draft',
                        'metadata' => json_encode([]),
                        'is_featured' => $c['is_featured'] ?? false,
                        'send_notifications' => true,
                        'track_conversions' => true,
                        'max_uses' => null,
                        'budget_limit' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Seed translations (lt as default, en as secondary)
                foreach ([
                    'lt' => $c['name'],
                    'en' => Str::title($slug),
                ] as $locale => $name) {
                    DB::table('campaign_translations')->updateOrInsert(
                        ['campaign_id' => $campaignId, 'locale' => $locale],
                        [
                            'name' => $name,
                            'slug' => Str::slug($name),
                            'description' => $locale === 'lt' ? 'Kampanijos aprašymas' : 'Campaign description',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                // Attach up to 6 discounts if any exist
                $discountIds = DB::table('discounts')->inRandomOrder()->limit(6)->pluck('id')->all();
                foreach ($discountIds as $did) {
                    $exists = DB::table('campaign_discount')->where('campaign_id', $campaignId)->where('discount_id', $did)->exists();
                    if (! $exists) {
                        DB::table('campaign_discount')->insert([
                            'campaign_id' => $campaignId,
                            'discount_id' => $did,
                        ]);
                    }
                }
            }
        });
    }
}

