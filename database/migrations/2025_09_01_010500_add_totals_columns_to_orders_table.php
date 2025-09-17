<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = array_filter(['sh_orders', 'orders'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $columns = [
                    'subtotal_amount',
                    'discount_total_amount',
                    'tax_total_amount',
                    'shipping_total_amount',
                    'grand_total_amount',
                ];
                foreach ($columns as $col) {
                    if (!Schema::hasColumn($tableName, $col)) {
                        $table->decimal($col, 15, 2)->default(0);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $tables = array_filter(['sh_orders', 'orders'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $columns = [
                    'grand_total_amount',
                    'shipping_total_amount',
                    'tax_total_amount',
                    'discount_total_amount',
                    'subtotal_amount',
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
