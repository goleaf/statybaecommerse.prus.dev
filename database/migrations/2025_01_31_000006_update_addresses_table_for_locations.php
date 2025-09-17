<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table): void {
                foreach (['country_id', 'zone_id', 'region_id', 'city_id'] as $col) {
                    if (!Schema::hasColumn('addresses', $col)) {
                        $table->unsignedBigInteger($col)->nullable()->after('country_code');
                    }
                }
                try {
                    $table->index(['country_id', 'zone_id', 'region_id', 'city_id'], 'addresses_geo_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table): void {
                try {
                    $table->dropIndex('addresses_geo_idx');
                } catch (\Throwable $e) {
                }
                foreach (['country_id', 'zone_id', 'region_id', 'city_id'] as $col) {
                    if (Schema::hasColumn('addresses', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
