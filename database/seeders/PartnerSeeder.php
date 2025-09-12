<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Ensure tiers exist using current schema
            $tiers = [
                ['name' => 'Gold', 'code' => 'gold', 'discount_rate' => 0.20, 'commission_rate' => 0.02, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => true]],
                ['name' => 'Silver', 'code' => 'silver', 'discount_rate' => 0.12, 'commission_rate' => 0.015, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => false]],
                ['name' => 'Bronze', 'code' => 'bronze', 'discount_rate' => 0.05, 'commission_rate' => 0.01, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => false]],
            ];

            foreach ($tiers as $t) {
                if (Schema::hasTable('partner_tiers')) {
                    DB::table('partner_tiers')->updateOrInsert(['code' => $t['code']], array_merge($t, [
                        'created_at' => now(), 'updated_at' => now(),
                    ]));
                } elseif (Schema::hasTable('sh_partner_tiers')) {
                    // Fallback legacy table
                    DB::table('sh_partner_tiers')->updateOrInsert(['name' => $t['name']], [
                        'priority' => 10,
                        'default_discount_pct' => 100 * ($t['discount_rate'] ?? 0),
                        'metadata' => json_encode([]),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            // Seed partners
            $partners = [
                ['name' => 'Acme', 'code' => 'acme', 'tier_code' => 'gold'],
                ['name' => 'Globex', 'code' => 'globex', 'tier_code' => 'silver'],
                ['name' => 'Initech', 'code' => 'initech', 'tier_code' => 'bronze'],
                ['name' => 'Umbrella', 'code' => 'umbrella', 'tier_code' => 'silver'],
                ['name' => 'Soylent', 'code' => 'soylent', 'tier_code' => 'bronze'],
            ];

            foreach ($partners as $p) {
                $tierId = null;
                if (Schema::hasTable('partner_tiers')) {
                    $tierId = DB::table('partner_tiers')->where('code', $p['tier_code'])->value('id');
                }

                if (Schema::hasTable('partners')) {
                    DB::table('partners')->updateOrInsert(
                        ['code' => $p['code']],
                        [
                            'name' => $p['name'],
                            'tier_id' => $tierId,
                            'contact_email' => $p['code'].'@example.test',
                            'contact_phone' => '+370600'.str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT),
                            'is_enabled' => true,
                            'discount_rate' => 0,
                            'commission_rate' => 0,
                            'metadata' => json_encode([]),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                } elseif (Schema::hasTable('sh_partners')) {
                    // Fallback legacy table
                    DB::table('sh_partners')->updateOrInsert(
                        ['code' => $p['code']],
                        [
                            'name' => $p['name'],
                            'tier' => $p['tier_code'] ?? 'custom',
                            'user_id' => null,
                            'metadata' => json_encode([]),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            // Assign some users to partners (if pivot exists)
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $userIds = DB::table('users')->inRandomOrder()->limit(10)->pluck('id')->all();
                $partnerIds = [];
                if (Schema::hasTable('partners')) {
                    $partnerIds = DB::table('partners')->pluck('id')->all();
                } elseif (Schema::hasTable('sh_partners')) {
                    $partnerIds = DB::table('sh_partners')->pluck('id')->all();
                }
                $userCount = count($userIds);
                foreach ($partnerIds as $partnerId) {
                    if ($userCount === 0) {
                        break;
                    }
                    $max = max(1, $userCount);
                    $take = ($userCount >= 2) ? random_int(2, $max) : 1;
                    foreach (array_slice($userIds, 0, $take) as $userId) {
                        $table = Schema::hasTable('partner_users') ? 'partner_users' : (Schema::hasTable('sh_partner_users') ? 'sh_partner_users' : null);
                        if (! $table) {
                            break 2;
                        }
                        $exists = DB::table($table)->where('partner_id', $partnerId)->where('user_id', $userId)->exists();
                        if (! $exists) {
                            DB::table($table)->insert([
                                'partner_id' => $partnerId,
                                'user_id' => $userId,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
