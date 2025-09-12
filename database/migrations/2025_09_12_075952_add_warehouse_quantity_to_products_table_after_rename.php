<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'warehouse_quantity')) {
                $table->integer('warehouse_quantity')->nullable()->after('stock_quantity');
                $table->index('warehouse_quantity', 'products_warehouse_qty_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'warehouse_quantity')) {
                $table->dropIndex('products_warehouse_qty_idx');
                $table->dropColumn('warehouse_quantity');
            }
        });
    }
};
