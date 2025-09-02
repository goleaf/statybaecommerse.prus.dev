<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_price_lists')) {
            $this->createIndex('sh_price_lists', 'sh_price_lists_currency_zone_priority', ['currency_id', 'zone_id', 'priority']);
            $this->createIndex('sh_price_lists', 'sh_price_lists_is_enabled', ['is_enabled']);
        }

        if (Schema::hasTable('sh_group_price_list')) {
            $this->createIndex('sh_group_price_list', 'idx_gpl_group_price', ['group_id', 'price_list_id']);
        }
        if (Schema::hasTable('sh_partner_price_list')) {
            $this->createIndex('sh_partner_price_list', 'idx_ppl_partner_price', ['partner_id', 'price_list_id']);
        }

        if (Schema::hasTable('sh_customer_group_user')) {
            $this->createIndex('sh_customer_group_user', 'idx_cgu_group_user', ['group_id', 'user_id']);
            $this->createIndex('sh_customer_group_user', 'idx_cgu_user', ['user_id']);
        }
        if (Schema::hasTable('sh_partner_users')) {
            $this->createIndex('sh_partner_users', 'idx_pu_partner_user', ['partner_id', 'user_id']);
            $this->createIndex('sh_partner_users', 'idx_pu_user', ['user_id']);
        }

        if (Schema::hasTable('sh_price_list_items')) {
            $this->createIndex('sh_price_list_items', 'idx_pli_price_product', ['price_list_id', 'product_id']);
        }

        if (Schema::hasTable('sh_currencies')) {
            $this->createIndex('sh_currencies', 'idx_currencies_code', ['code']);
        }
    }

    public function down(): void {}

    private function createIndex(string $table, string $indexName, array $columns): void
    {
        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                $cols = '`' . implode('`,`', $columns) . '`';
                DB::statement("CREATE INDEX IF NOT EXISTS `{$indexName}` ON `{$table}` ({$cols})");
            } elseif ($driver === 'sqlite') {
                DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} (" . implode(',', $columns) . ")");
            } else {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    try { $t->index($columns, $indexName); } catch (Throwable $e) {}
                });
            }
        } catch (Throwable $e) {}
    }
};


