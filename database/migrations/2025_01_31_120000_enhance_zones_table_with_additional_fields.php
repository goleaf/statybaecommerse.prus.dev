<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (!Schema::hasColumn('zones', 'type')) {
                    $table->string('type')->default('shipping')->after('code');
                }
                if (!Schema::hasColumn('zones', 'priority')) {
                    $table->integer('priority')->default(0)->after('sort_order');
                }
                if (!Schema::hasColumn('zones', 'min_order_amount')) {
                    $table->decimal('min_order_amount', 10, 2)->nullable()->after('shipping_rate');
                }
                if (!Schema::hasColumn('zones', 'max_order_amount')) {
                    $table->decimal('max_order_amount', 10, 2)->nullable()->after('min_order_amount');
                }
                if (!Schema::hasColumn('zones', 'free_shipping_threshold')) {
                    $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('max_order_amount');
                }
                if (!Schema::hasColumn('zones', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_default');
                }

                // Add indexes for better performance
                try {
                    $table->index(['type', 'is_active']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['currency_id', 'is_active']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['priority', 'sort_order']);
                } catch (\Throwable $e) {
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                try {
                    $table->dropIndex(['type', 'is_active']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['currency_id', 'is_active']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['priority', 'sort_order']);
                } catch (\Throwable $e) {
                }

                $columns = [
                    'type',
                    'priority',
                    'min_order_amount',
                    'max_order_amount',
                    'free_shipping_threshold',
                    'is_active',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('zones', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
