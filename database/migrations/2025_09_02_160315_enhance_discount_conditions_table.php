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
        if (! Schema::hasTable('discount_conditions')) {
            return;
        }

        Schema::table('discount_conditions', function (Blueprint $table) {
            // Add missing fields if they don't exist
            if (! Schema::hasColumn('discount_conditions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('position');
            }

            if (! Schema::hasColumn('discount_conditions', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }

            if (! Schema::hasColumn('discount_conditions', 'metadata')) {
                $table->json('metadata')->nullable()->after('priority');
            }

            // Add indexes for performance
            $table->index(['is_active'], 'discount_conditions_is_active_index');
            $table->index(['priority'], 'discount_conditions_priority_index');
            $table->index(['type'], 'discount_conditions_type_index');
            $table->index(['operator'], 'discount_conditions_operator_index');
            $table->index(['discount_id', 'is_active'], 'discount_conditions_discount_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('discount_conditions')) {
            return;
        }

        Schema::table('discount_conditions', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('discount_conditions_is_active_index');
            $table->dropIndex('discount_conditions_priority_index');
            $table->dropIndex('discount_conditions_type_index');
            $table->dropIndex('discount_conditions_operator_index');
            $table->dropIndex('discount_conditions_discount_active_index');

            // Drop columns
            if (Schema::hasColumn('discount_conditions', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('discount_conditions', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('discount_conditions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
