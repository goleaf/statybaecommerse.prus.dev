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
        Schema::table('zones', function (Blueprint $table) {
            $table->string('type')->default('shipping')->after('code');
            $table->integer('priority')->default(0)->after('sort_order');
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('shipping_rate');
            $table->decimal('max_order_amount', 10, 2)->nullable()->after('min_order_amount');
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('max_order_amount');
            $table->boolean('is_active')->default(true)->after('is_default');
            
            // Add indexes for better performance
            $table->index(['type', 'is_active']);
            $table->index(['currency_id', 'is_active']);
            $table->index(['priority', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropIndex(['type', 'is_active']);
            $table->dropIndex(['currency_id', 'is_active']);
            $table->dropIndex(['priority', 'sort_order']);
            
            $table->dropColumn([
                'type',
                'priority',
                'min_order_amount',
                'max_order_amount',
                'free_shipping_threshold',
                'is_active',
            ]);
        });
    }
};

