<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\LazyCollection;
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

        // Use timeout protection for large customer seeding operations
        $timeout = now()->addMinutes(30); // 30 minute timeout for bulk customer seeding

        LazyCollection::make(range($baseIndex, $targetCount))
            ->takeUntilTimeout($timeout)
            ->chunk($chunkSize)
            ->each(function ($chunk) use ($now, &$baseIndex) {
                $baseIndex = $chunk->first();
                $end = $chunk->last();

                // Create users using factory in batches
                $users = collect();
                for ($i = $baseIndex; $i <= $end; $i++) {
                    $user = User::factory()
                        ->state([
                            'name' => 'Customer '.$i,
                            'email' => sprintf('customer%05d@example.com', $i),
                            'preferred_locale' => ($i % 2) === 0 ? 'lt' : 'en',
                            'is_admin' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])
                        ->create();
                    
                    $users->push($user);
                }

                // advance users bar
                if (isset($usersBar)) {
                    $usersBar->advance($users->count());
                }

                $insertedUsers = $users;

                // Create addresses using factory and relationships
                if ($this->tableExists('addresses')) {
                    foreach ($insertedUsers as $user) {
                        // Create shipping address using factory
                        Address::factory()
                            ->for($user)
                            ->state([
                                'type' => 'shipping',
                                'is_default' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ])
                            ->create();

                        // Create billing address using factory
                        Address::factory()
                            ->for($user)
                            ->state([
                                'type' => 'billing',
                                'is_default' => false,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ])
                            ->create();
                    }
                    
                    if (isset($addressesBar)) {
                        $addressesBar->advance($insertedUsers->count() * 2);
                    }
                }

                // Assign to customer group using relationships
                if ($this->tableExists('customer_groups') && $this->tableExists('customer_group_user')) {
                    $customerGroup = CustomerGroup::first();
                    if ($customerGroup) {
                        // init groups bar on first use
                        if ($this->command && ! isset($groupsBar)) {
                            $groupsBar = $this->command->getOutput()->createProgressBar(100);
                            $groupsBar->setFormat('Groups: %current%/%max% [%bar%] %percent:3s%%');
                            $this->command->line('');
                            $groupsBar->start();
                        }
                        
                        foreach ($insertedUsers as $user) {
                            // Use model relationship to attach user to group
                            $customerGroup->users()->attach($user->id, [
                                'assigned_at' => $now,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                        
                        if (isset($groupsBar)) {
                            $groupsBar->advance(count($insertedUsers));
                        }
                    }
                }

                $baseIndex = $end + 1;
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
