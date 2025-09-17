<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['sh_products', 'products'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    if (!Schema::hasColumn($tableName, 'warehouse_quantity')) {
                        $table->integer('warehouse_quantity')->nullable();
                    }
                    try {
                        $table->index('warehouse_quantity', $tableName . '_warehouse_qty_idx');
                    } catch (\Throwable $e) {
                    }
                });
                break;
            }
        }
    }

    public function down(): void
    {
        foreach (['sh_products', 'products'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    try {
                        $table->dropIndex($tableName . '_warehouse_qty_idx');
                    } catch (\Throwable $e) {
                    }
                    if (Schema::hasColumn($tableName, 'warehouse_quantity')) {
                        $table->dropColumn('warehouse_quantity');
                    }
                });
                break;
            }
        }
    }
};
