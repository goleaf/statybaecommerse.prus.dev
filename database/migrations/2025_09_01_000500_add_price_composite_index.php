<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_prices')) {
            return;
        }
        $driver = DB::getDriverName();
        $index = 'idx_prices_priceable_currency';
        try {
            if ($driver === 'mysql') {
                DB::statement('CREATE INDEX IF NOT EXISTS `' . $index . '` ON `sh_prices` (`priceable_type`,`priceable_id`,`currency_id`)');
            } elseif ($driver === 'sqlite') {
                DB::statement('CREATE INDEX IF NOT EXISTS ' . $index . ' ON sh_prices (priceable_type,priceable_id,currency_id)');
            } else {
                Schema::table('sh_prices', function ($table) use ($index) {
                    try {
                        $table->index(['priceable_type', 'priceable_id', 'currency_id'], $index);
                    } catch (Throwable $e) {
                    }
                });
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void {}
};
