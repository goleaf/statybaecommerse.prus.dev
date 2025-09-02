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
            $name = 'Black Friday';
            $slug = Str::slug($name);
            $campaignId = DB::table('sh_discount_campaigns')->where('slug', $slug)->value('id');
            if (! $campaignId) {
                $campaignId = DB::table('sh_discount_campaigns')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'starts_at' => now()->addMonth()->startOfMonth(),
                    'ends_at' => now()->addMonth()->endOfMonth(),
                    'channel_id' => null,
                    'zone_id' => null,
                    'status' => 'scheduled',
                    'metadata' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Attach up to 6 discounts if any exist
            $discountIds = DB::table('sh_discounts')->inRandomOrder()->limit(6)->pluck('id')->all();
            foreach ($discountIds as $did) {
                $exists = DB::table('sh_campaign_discount')->where('campaign_id', $campaignId)->where('discount_id', $did)->exists();
                if (! $exists) {
                    DB::table('sh_campaign_discount')->insert([
                        'campaign_id' => $campaignId,
                        'discount_id' => $did,
                    ]);
                }
            }
        });
    }
}


