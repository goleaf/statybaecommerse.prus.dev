<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_prices')) {
            Schema::table('sh_prices', function ($table) {
                try {
                    $table->index(['priceable_type', 'priceable_id', 'currency_id'], 'idx_prices_priceable_currency');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sh_prices')) {
            Schema::table('sh_prices', function ($table) {
                try {
                    $table->dropIndex('idx_prices_priceable_currency');
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
