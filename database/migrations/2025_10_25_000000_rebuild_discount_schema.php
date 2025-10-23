<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildDiscountCodes();
        $this->rebuildDiscountRedemptions();
        $this->rebuildCampaignDiscountPivot();
    }

    public function down(): void
    {
        // The schema upgrades performed here are not trivially reversible
        // without risking data loss. Existing installations should restore
        // from a database backup if a rollback is required.
    }

    private function rebuildDiscountCodes(): void
    {
        if (! Schema::hasTable('discount_codes')) {
            return;
        }

        if (
            $this->hasForeignKey('discount_codes', 'discount_id', 'discounts')
            && $this->hasForeignKey('discount_codes', 'customer_group_id', 'customer_groups')
            && $this->hasForeignKey('discount_codes', 'created_by', 'users')
            && $this->hasForeignKey('discount_codes', 'updated_by', 'users')
        ) {
            return;
        }

        $legacyTable = 'discount_codes_legacy';

        $this->dropSqliteIndexes('discount_codes', [
            'discount_codes_code_unique',
            'discount_codes_active_window_idx',
            'discount_codes_discount_code_idx',
            'discount_codes_customer_status_idx',
            'discount_codes_valid_window_idx',
            'discount_codes_created_by_index',
            'discount_codes_updated_by_index',
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists($legacyTable);
            Schema::rename('discount_codes', $legacyTable);

            $this->dropIndexIfExists('discount_codes_code_unique', $legacyTable);

            Schema::create('discount_codes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
                $table->string('code');
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->text('description_lt')->nullable();
                $table->text('description_en')->nullable();
                $table->string('type')->default('percentage');
                $table->decimal('value', 10, 2)->default(0);
                $table->decimal('minimum_amount', 10, 2)->default(0);
                $table->decimal('maximum_discount', 10, 2)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_limit_per_user')->nullable();
                $table->integer('usage_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_public')->default(false);
                $table->boolean('is_auto_apply')->default(false);
                $table->boolean('is_stackable')->default(false);
                $table->boolean('is_first_time_only')->default(false);
                $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
                $table->string('status')->default('inactive');
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('code', 'discount_codes_code_unique_new');
                $table->index(['is_active', 'status', 'starts_at', 'expires_at'], 'discount_codes_active_window_idx');
                $table->index(['discount_id', 'code'], 'discount_codes_discount_code_idx');
                $table->index(['customer_group_id', 'status'], 'discount_codes_customer_status_idx');
                $table->index(['valid_from', 'valid_until'], 'discount_codes_valid_window_idx');
                $table->index(['created_by']);
                $table->index(['updated_by']);
            });

            $this->copyDiscountCodes($legacyTable, 'discount_codes');

            Schema::dropIfExists($legacyTable);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function rebuildDiscountRedemptions(): void
    {
        if (! Schema::hasTable('discount_redemptions')) {
            Schema::create('discount_redemptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
                $table->foreignId('code_id')->nullable()->constrained('discount_codes')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('amount_saved', 12, 2)->default(0);
                $table->char('currency_code', 3)->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->string('status')->default('pending');
                $table->string('notes')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['discount_id', 'status'], 'discount_redemptions_discount_status_idx');
                $table->index(['code_id', 'user_id'], 'discount_redemptions_code_user_idx');
                $table->index(['user_id', 'status'], 'discount_redemptions_user_status_idx');
                $table->index(['order_id', 'status'], 'discount_redemptions_order_status_idx');
                $table->index(['redeemed_at']);
                $table->index(['created_by']);
                $table->index(['updated_by']);
            });

            return;
        }

        if (
            $this->hasForeignKey('discount_redemptions', 'discount_id', 'discounts')
            && $this->hasForeignKey('discount_redemptions', 'code_id', 'discount_codes')
            && $this->hasForeignKey('discount_redemptions', 'order_id', 'orders')
            && $this->hasForeignKey('discount_redemptions', 'user_id', 'users')
        ) {
            return;
        }

        $legacyTable = 'discount_redemptions_legacy';
        $legacyTranslations = 'discount_redemption_translations_legacy';
        $hasTranslations = Schema::hasTable('discount_redemption_translations');

        $this->dropSqliteIndexes('discount_redemptions', [
            'discount_redemptions_discount_status_idx',
            'discount_redemptions_code_user_idx',
            'discount_redemptions_user_status_idx',
            'discount_redemptions_order_status_idx',
            'discount_redemptions_redeemed_at_index',
            'discount_redemptions_created_by_index',
            'discount_redemptions_updated_by_index',
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists($legacyTranslations);
            Schema::dropIfExists($legacyTable);

            if ($hasTranslations) {
                $this->dropSqliteIndexes('discount_redemption_translations', [
                    'discount_redemption_translations_discount_redemption_id_locale_unique',
                    'discount_redemption_translations_locale_index',
                ]);

                Schema::rename('discount_redemption_translations', $legacyTranslations);
            }

            Schema::rename('discount_redemptions', $legacyTable);

            Schema::create('discount_redemptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
                $table->foreignId('code_id')->nullable()->constrained('discount_codes')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('amount_saved', 12, 2)->default(0);
                $table->char('currency_code', 3)->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->string('status')->default('pending');
                $table->string('notes')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['discount_id', 'status'], 'discount_redemptions_discount_status_idx');
                $table->index(['code_id', 'user_id'], 'discount_redemptions_code_user_idx');
                $table->index(['user_id', 'status'], 'discount_redemptions_user_status_idx');
                $table->index(['order_id', 'status'], 'discount_redemptions_order_status_idx');
                $table->index(['redeemed_at']);
                $table->index(['created_by']);
                $table->index(['updated_by']);
            });

            $this->copyDiscountRedemptions($legacyTable, 'discount_redemptions');

            Schema::dropIfExists($legacyTable);

            if ($hasTranslations) {
                Schema::create('discount_redemption_translations', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('discount_redemption_id')->constrained('discount_redemptions')->cascadeOnDelete();
                    $table->string('locale', 5);
                    $table->text('notes')->nullable();
                    $table->string('status_description')->nullable();
                    $table->json('metadata_description')->nullable();
                    $table->timestamps();

                    $table->unique(['discount_redemption_id', 'locale']);
                    $table->index(['locale']);
                });

                $this->copyTable($legacyTranslations, 'discount_redemption_translations', [
                    'id' => null,
                    'discount_redemption_id' => null,
                    'locale' => null,
                    'notes' => null,
                    'status_description' => null,
                    'metadata_description' => null,
                    'created_at' => null,
                    'updated_at' => null,
                ]);

                Schema::dropIfExists($legacyTranslations);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function rebuildCampaignDiscountPivot(): void
    {
        if (! Schema::hasTable('campaign_discount')) {
            return;
        }

        if (
            $this->hasForeignKey('campaign_discount', 'campaign_id', 'discount_campaigns')
            && $this->hasForeignKey('campaign_discount', 'discount_id', 'discounts')
        ) {
            return;
        }

        $legacyTable = 'campaign_discount_legacy';

        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists($legacyTable);
            Schema::rename('campaign_discount', $legacyTable);

            Schema::create('campaign_discount', function (Blueprint $table): void {
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
                $table->primary(['campaign_id', 'discount_id']);
                $table->index('discount_id', 'campaign_discount_discount_idx');
            });

            $this->copyTable($legacyTable, 'campaign_discount', [
                'campaign_id' => null,
                'discount_id' => null,
            ]);

            Schema::dropIfExists($legacyTable);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function copyDiscountCodes(string $from, string $to): void
    {
        $available = array_flip(Schema::getColumnListing($from));

        DB::table($from)->orderBy('id')->chunk(200, function ($rows) use ($available, $to): void {
            $batch = [];

            foreach ($rows as $row) {
                $record = [
                    'id' => $this->value($row, $available, 'id'),
                    'discount_id' => $this->value($row, $available, 'discount_id'),
                    'code' => $this->value($row, $available, 'code'),
                    'name' => $this->value($row, $available, 'name'),
                    'description' => $this->value($row, $available, 'description'),
                    'description_lt' => $this->value($row, $available, 'description_lt'),
                    'description_en' => $this->value($row, $available, 'description_en'),
                    'type' => $this->value($row, $available, 'type', 'percentage'),
                    'value' => $this->value($row, $available, 'value', 0),
                    'minimum_amount' => $this->value($row, $available, 'minimum_amount', 0),
                    'maximum_discount' => $this->value($row, $available, 'maximum_discount'),
                    'starts_at' => $this->value($row, $available, 'starts_at'),
                    'expires_at' => $this->value($row, $available, 'expires_at'),
                    'valid_from' => $this->value($row, $available, 'valid_from'),
                    'valid_until' => $this->value($row, $available, 'valid_until'),
                    'usage_limit' => $this->value($row, $available, 'usage_limit', $this->value($row, $available, 'max_uses')),
                    'usage_limit_per_user' => $this->value($row, $available, 'usage_limit_per_user'),
                    'usage_count' => $this->value($row, $available, 'usage_count', 0),
                    'is_active' => $this->value($row, $available, 'is_active', true),
                    'is_public' => $this->value($row, $available, 'is_public', false),
                    'is_auto_apply' => $this->value($row, $available, 'is_auto_apply', false),
                    'is_stackable' => $this->value($row, $available, 'is_stackable', false),
                    'is_first_time_only' => $this->value($row, $available, 'is_first_time_only', false),
                    'customer_group_id' => $this->value($row, $available, 'customer_group_id'),
                    'status' => $this->value($row, $available, 'status', 'inactive'),
                    'metadata' => $this->value($row, $available, 'metadata'),
                    'created_by' => $this->value($row, $available, 'created_by'),
                    'updated_by' => $this->value($row, $available, 'updated_by'),
                    'created_at' => $this->value($row, $available, 'created_at'),
                    'updated_at' => $this->value($row, $available, 'updated_at'),
                    'deleted_at' => $this->value($row, $available, 'deleted_at'),
                ];

                if ($record['code'] === null) {
                    continue;
                }

                $batch[] = $record;
            }

            if ($batch !== []) {
                DB::table($to)->insert($batch);
            }
        });
    }

    private function copyDiscountRedemptions(string $from, string $to): void
    {
        $available = array_flip(Schema::getColumnListing($from));

        DB::table($from)->orderBy('id')->chunk(200, function ($rows) use ($available, $to): void {
            $batch = [];

            foreach ($rows as $row) {
                $redeemedAt = $this->value($row, $available, 'redeemed_at');
                $status = $this->value($row, $available, 'status');

                if ($status === null) {
                    $status = $redeemedAt !== null ? 'redeemed' : 'pending';
                }

                $record = [
                    'id' => $this->value($row, $available, 'id'),
                    'discount_id' => $this->value($row, $available, 'discount_id'),
                    'code_id' => $this->value($row, $available, 'code_id'),
                    'order_id' => $this->value($row, $available, 'order_id'),
                    'user_id' => $this->value($row, $available, 'user_id'),
                    'amount_saved' => $this->value($row, $available, 'amount_saved', 0),
                    'currency_code' => $this->value($row, $available, 'currency_code'),
                    'redeemed_at' => $redeemedAt,
                    'status' => $status,
                    'notes' => $this->value($row, $available, 'notes'),
                    'ip_address' => $this->value($row, $available, 'ip_address'),
                    'user_agent' => $this->value($row, $available, 'user_agent'),
                    'metadata' => $this->value($row, $available, 'metadata'),
                    'created_by' => $this->value($row, $available, 'created_by'),
                    'updated_by' => $this->value($row, $available, 'updated_by'),
                    'created_at' => $this->value($row, $available, 'created_at'),
                    'updated_at' => $this->value($row, $available, 'updated_at'),
                    'deleted_at' => $this->value($row, $available, 'deleted_at'),
                ];

                $batch[] = $record;
            }

            if ($batch !== []) {
                DB::table($to)->insert($batch);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $columns
     */
    private function copyTable(string $from, string $to, array $columns): void
    {
        if (! Schema::hasTable($from)) {
            return;
        }

        $available = array_flip(Schema::getColumnListing($from));

        DB::table($from)->orderBy('id')->chunk(200, function ($rows) use ($available, $columns, $to): void {
            $batch = [];

            foreach ($rows as $row) {
                $record = [];

                foreach ($columns as $column => $default) {
                    $record[$column] = $this->value($row, $available, $column, $default);
                }

                $batch[] = $record;
            }

            if ($batch !== []) {
                DB::table($to)->insert($batch);
            }
        });
    }

    private function dropIndexIfExists(string $indexName, string $table): void
    {
        $connection = Schema::getConnection()->getDriverName();

        try {
            if ($connection === 'mysql') {
                DB::statement(sprintf('DROP INDEX %s ON %s', $indexName, $table));

                return;
            }

            DB::statement(sprintf('DROP INDEX IF EXISTS %s', $indexName));
        } catch (\Throwable) {
            // Index was already removed or the driver does not support conditional drops.
        }
    }

    /**
     * @param  array<string, int>  $available
     */
    private function value(object $row, array $available, string $column, mixed $default = null): mixed
    {
        return array_key_exists($column, $available) ? $row->{$column} : $default;
    }

    private function hasForeignKey(string $table, string $column, string $referencedTable): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($foreignKeys as $foreignKey) {
                if (
                    isset($foreignKey->from, $foreignKey->table)
                    && strcasecmp((string) $foreignKey->from, $column) === 0
                    && strcasecmp((string) $foreignKey->table, $referencedTable) === 0
                ) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();

            return DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->where('REFERENCED_TABLE_NAME', $referencedTable)
                ->exists();
        }

        return false;
    }

    /**
     * SQLite requires globally unique index names, so remove existing ones before recreating tables.
     *
     * @param  array<int, string>  $indexes
     */
    private function dropSqliteIndexes(string $table, array $indexes): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($indexes as $index) {
            try {
                DB::statement(sprintf('DROP INDEX IF EXISTS "%s"', $index));
            } catch (\Throwable) {
                // Ignore drop failures to keep migrations resilient across environments.
            }
        }
    }
};
