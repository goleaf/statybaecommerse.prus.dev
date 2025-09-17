<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $groups = [
                ['name' => 'VIP', 'code' => 'vip'],
                ['name' => 'Student', 'code' => 'student'],
                ['name' => 'Wholesale', 'code' => 'wholesale'],
            ];

            foreach ($groups as $g) {
                $exists = DB::table('sh_customer_groups')->where('code', $g['code'])->exists();
                if (! $exists) {
                    DB::table('sh_customer_groups')->insert([
                        'name' => $g['name'],
                        'code' => $g['code'],
                        'metadata' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach random existing users to groups (if users table exists)
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $userIds = DB::table('users')->inRandomOrder()->limit(20)->pluck('id')->all();
                $groupIds = DB::table('sh_customer_groups')->pluck('id')->all();
                foreach ($groupIds as $groupId) {
                    $count = count($userIds);
                    if ($count === 0) {
                        continue;
                    }
                    $take = min($count, random_int(1, min(5, $count)));
                    foreach (array_slice($userIds, 0, $take) as $userId) {
                        $exists = DB::table('sh_customer_group_user')
                            ->where('group_id', $groupId)
                            ->where('user_id', $userId)
                            ->exists();
                        if (! $exists) {
                            DB::table('sh_customer_group_user')->insert([
                                'group_id' => $groupId,
                                'user_id' => $userId,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
