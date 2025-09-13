<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class BulkCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $targetCount = (int) env('BULK_CUSTOMER_COUNT', 100);
        $now = now()->toDateTimeString();

        // --- Plan output (X / X) ---
        $totalSteps = 5;  // 1:indexes, 2:users, 3:addresses, 4:groups, 5:finalize
        if ($this->command) {
            $this->command->info('📋 Plan:');
            $this->command->line("  1/{$totalSteps} Ensure indexes for users and related tables");
            $this->command->line("  2/{$totalSteps} Insert users ({$targetCount})");
            $this->command->line("  3/{$totalSteps} Insert addresses (shipping + billing)");
            $this->command->line("  4/{$totalSteps} Attach users to default customer group (if exists)");
            $this->command->line("  5/{$totalSteps} Finalize and summary");
        }

        // Step progress bar
        $stepBar = null;
        if ($this->command) {
            $stepBar = $this->command->getOutput()->createProgressBar($totalSteps);
            $stepBar->start();
        }

        // Step 1: ensure indexes
        $this->ensureIndexes();
        $stepBar?->advance();

        $chunkSize = 500;
        $baseIndex = 1;

        // Detailed progress bars
        $usersBar = null;
        $addressesBar = null;
        $groupsBar = null;
        if ($this->command) {
            $usersBar = $this->command->getOutput()->createProgressBar($targetCount);
            $usersBar->setFormat('Users: %current%/%max% [%bar%] %percent:3s%%');
            $this->command->line('');
            $usersBar->start();

            $addressesBar = $this->command->getOutput()->createProgressBar($targetCount * 2);
            $addressesBar->setFormat('Addresses: %current%/%max% [%bar%] %percent:3s%%');
            $this->command->line('');
            $addressesBar->start();
        }

        DB::transaction(function () use ($targetCount, $chunkSize, $now, &$baseIndex): void {
            while ($baseIndex <= $targetCount) {
                $end = min($baseIndex + $chunkSize - 1, $targetCount);

                // Users batch
                $usersBatch = [];
                for ($i = $baseIndex; $i <= $end; $i++) {
                    $name = 'Customer '.$i;
                    $usersBatch[] = [
                        'name' => $name,
                        'email' => sprintf('customer%05d@example.com', $i),
                        'email_verified_at' => $now,
                        'password' => Hash::make('password'),
                        'preferred_locale' => ($i % 2) === 0 ? 'lt' : 'en',
                        'remember_token' => Str::random(10),
                        'is_admin' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                // Use upsert to handle existing records
                DB::table('users')->upsert($usersBatch, ['email'], ['name', 'password', 'preferred_locale', 'remember_token', 'is_admin', 'updated_at']);
                // advance users bar
                if (isset($usersBar)) {
                    $usersBar->advance(count($usersBatch));
                }

                // Resolve IDs
                $emails = array_map(fn ($i) => sprintf('customer%05d@example.com', $i), range($baseIndex, $end));
                $insertedUsers = DB::table('users')->whereIn('email', $emails)->select('id', 'name', 'email')->get();

                // Addresses batch
                if ($this->tableExists('addresses')) {
                    $addresses = [];
                    $hasCountryCode = $this->columnExists('addresses', 'country_code');
                    $hasCountry = $this->columnExists('addresses', 'country');
                    foreach ($insertedUsers as $u) {
                        $firstLast = explode(' ', (string) $u->name, 2);
                        $first = $firstLast[0] ?? 'Customer';
                        $last = $firstLast[1] ?? 'User';
                        $countryValue = 'LT';
                        $base = [
                            'user_id' => $u->id,
                            'first_name' => $first,
                            'last_name' => $last,
                            'address_line_1' => 'Gedimino pr. 1',
                            'city' => 'Vilnius',
                            'postal_code' => '01103',
                            'phone' => '+37060000000',
                            'is_default' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        if ($hasCountryCode) {
                            $base['country_code'] = $countryValue;
                        }
                        if ($hasCountry) {
                            $base['country'] = $countryValue;
                        }

                        $addresses[] = array_merge($base, ['type' => 'shipping']);
                        $addresses[] = array_merge($base, ['type' => 'billing', 'is_default' => false]);
                    }
                    if (! empty($addresses)) {
                        DB::table('addresses')->insert($addresses);
                        if (isset($addressesBar)) {
                            $addressesBar->advance(count($addresses));
                        }
                    }
                }

                // Assign to one customer group if present
                if ($this->tableExists('customer_groups') && $this->tableExists('customer_group_user')) {
                    $groupId = DB::table('customer_groups')->value('id');
                    if ($groupId) {
                        // init groups bar on first use
                        if ($this->command && ! isset($groupsBar)) {
                            $groupsBar = $this->command->getOutput()->createProgressBar($targetCount);
                            $groupsBar->setFormat('Groups: %current%/%max% [%bar%] %percent:3s%%');
                            $this->command->line('');
                            $groupsBar->start();
                        }
                        $pivot = [];
                        foreach ($insertedUsers as $u) {
                            $pivot[] = [
                                'customer_group_id' => $groupId,
                                'user_id' => $u->id,
                                'assigned_at' => $now,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        DB::table('customer_group_user')->insertOrIgnore($pivot);
                        if (isset($groupsBar)) {
                            $groupsBar->advance(count($insertedUsers));
                        }
                    }
                }

                $baseIndex = $end + 1;
            }
        });

        // Finish detailed bars and step 2–4
        if (isset($usersBar)) {
            $usersBar->finish();
            $this->command?->line('');
        }
        $stepBar?->advance();  // users
        if (isset($addressesBar)) {
            $addressesBar->finish();
            $this->command?->line('');
        }
        $stepBar?->advance();  // addresses
        if (isset($groupsBar)) {
            $groupsBar->finish();
            $this->command?->line('');
        }
        $stepBar?->advance();  // groups

        // Finalize
        $this->command?->info('✅ Customers seeding completed.');
        $stepBar?->advance();
        if ($stepBar) {
            $stepBar->finish();
            $this->command?->line('');
        }
    }

    private function ensureIndexes(): void
    {
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS users_locale_idx ON users (preferred_locale)');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS users_created_idx ON users (created_at)');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS users_admin_idx ON users (is_admin)');
        } catch (\Throwable $e) {
        }

        $map = [
            ['table' => 'orders', 'column' => 'user_id', 'name' => 'orders_user_idx'],
            ['table' => 'reviews', 'column' => 'user_id', 'name' => 'reviews_user_idx'],
            ['table' => 'addresses', 'column' => 'user_id', 'name' => 'addresses_user_idx'],
            ['table' => 'cart_items', 'column' => 'user_id', 'name' => 'cart_items_user_idx'],
            ['table' => 'user_wishlists', 'column' => 'user_id', 'name' => 'user_wishlists_user_idx'],
            ['table' => 'discount_redemptions', 'column' => 'user_id', 'name' => 'discount_redemptions_user_idx'],
            ['table' => 'customer_group_user', 'column' => 'user_id', 'name' => 'customer_group_user_user_idx'],
        ];
        foreach ($map as $ix) {
            if ($this->tableExists($ix['table']) && $this->columnExists($ix['table'], $ix['column'])) {
                try {
                    DB::statement("CREATE INDEX IF NOT EXISTS {$ix['name']} ON {$ix['table']} ({$ix['column']})");
                } catch (\Throwable $e) {
                }
            }
        }
        if ($this->tableExists('addresses') && $this->columnExists('addresses', 'type')) {
            try {
                DB::statement('CREATE INDEX IF NOT EXISTS addresses_user_type_idx ON addresses (user_id, type)');
            } catch (\Throwable $e) {
            }
        }
        if ($this->tableExists('user_wishlists') && $this->columnExists('user_wishlists', 'product_id')) {
            try {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS user_wishlists_unique ON user_wishlists (user_id, product_id)');
            } catch (\Throwable $e) {
            }
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
