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
        if (Schema::hasTable('cart_items') && ! Schema::hasColumn('cart_items', 'minimum_quantity')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->integer('minimum_quantity')->default(1)->after('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'minimum_quantity')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('minimum_quantity');
            });
        }
    }
};
