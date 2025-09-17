<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = array_filter(['sh_order_shippings', 'order_shippings'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (!Schema::hasColumn($tableName, 'tracking_number')) {
                    $table->string('tracking_number')->nullable()->after('carrier_name');
                }
                if (!Schema::hasColumn($tableName, 'tracking_url')) {
                    $table->string('tracking_url')->nullable()->after('tracking_number');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = array_filter(['sh_order_shippings', 'order_shippings'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                foreach (['tracking_url', 'tracking_number'] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
