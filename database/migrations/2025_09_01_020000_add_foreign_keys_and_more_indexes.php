<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->index('sh_orders', 'idx_orders_number_created', ['number', 'created_at']);
        $this->index('sh_order_items', 'idx_order_items_order_product', ['order_id', 'product_id']);
        $this->index('sh_prices', 'idx_prices_currency_amount', ['currency_id', 'amount']);
        $this->index('sh_inventories', 'idx_inventories_country_default', ['country_id', 'is_default']);
        $this->index('sh_zones', 'idx_zones_code', ['code']);

        if (Schema::hasTable('sh_order_items')) {
            Schema::table('sh_order_items', function (Blueprint $table) {
                if (! $this->fkExists('sh_order_items', 'sh_order_items_order_id_foreign')) {
                    try {
                        $table->foreign('order_id')->references('id')->on('sh_orders')->cascadeOnDelete();
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('sh_prices')) {
            Schema::table('sh_prices', function (Blueprint $table) {
                if (Schema::hasColumn('sh_prices', 'currency_id') && ! $this->fkExists('sh_prices', 'sh_prices_currency_id_foreign')) {
                    try {
                        $table->foreign('currency_id')->references('id')->on('sh_currencies');
                    } catch (\Throwable $e) {
                    }
                }
            });
        }
    }

    public function down(): void {}

    private function index(string $table, string $name, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $cols = '`'.implode('`,`', $columns).'`';
                DB::statement("CREATE INDEX IF NOT EXISTS `{$name}` ON `{$table}` ({$cols})");
            } else {
                Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                    try {
                        $t->index($columns, $name);
                    } catch (\Throwable $e) {
                    }
                });
            }
        } catch (\Throwable $e) {
        }
    }

    private function fkExists(string $table, string $fkName): bool
    {
        try {
            $driver = DB::getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $db = DB::getDatabaseName();
                $row = DB::selectOne('SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = "FOREIGN KEY" LIMIT 1', [$db, $table, $fkName]);

                return $row !== null;
            }
        } catch (\Throwable $e) {
        }

        return false;
    }
};
