<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tiers = [
                ['name' => 'Gold', 'priority' => 10, 'default_discount_pct' => 20.00],
                ['name' => 'Silver', 'priority' => 20, 'default_discount_pct' => 12.00],
                ['name' => 'Bronze', 'priority' => 30, 'default_discount_pct' => 5.00],
            ];

            foreach ($tiers as $t) {
                $exists = DB::table('sh_partner_tiers')->where('name', $t['name'])->exists();
                if (! $exists) {
                    DB::table('sh_partner_tiers')->insert(array_merge($t, [
                        'metadata' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }

            $partners = [
                ['name' => 'Acme', 'code' => 'acme', 'tier' => 'gold'],
                ['name' => 'Globex', 'code' => 'globex', 'tier' => 'silver'],
                ['name' => 'Initech', 'code' => 'initech', 'tier' => 'bronze'],
                ['name' => 'Umbrella', 'code' => 'umbrella', 'tier' => 'custom'],
                ['name' => 'Soylent', 'code' => 'soylent', 'tier' => 'custom'],
            ];

            foreach ($partners as $p) {
                $exists = DB::table('sh_partners')->where('code', $p['code'])->exists();
                if (! $exists) {
                    DB::table('sh_partners')->insert([
                        'name' => $p['name'],
                        'code' => $p['code'],
                        'tier' => $p['tier'],
                        'user_id' => null,
                        'metadata' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Assign some users to partners
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $userIds = DB::table('users')->inRandomOrder()->limit(10)->pluck('id')->all();
                $partnerIds = DB::table('sh_partners')->pluck('id')->all();
                $userCount = count($userIds);
                foreach ($partnerIds as $partnerId) {
                    if ($userCount === 0) {
                        break;
                    }
                    $max = max(1, $userCount);
                    $take = ($userCount >= 2) ? random_int(2, $max) : 1;
                    foreach (array_slice($userIds, 0, $take) as $userId) {
                        $exists = DB::table('sh_partner_users')->where('partner_id', $partnerId)->where('user_id', $userId)->exists();
                        if (! $exists) {
                            DB::table('sh_partner_users')->insert([
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


