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
        Schema::table('feature_flags', function (Blueprint $table) {
            // Add missing columns that the test expects
            if (! Schema::hasColumn('feature_flags', 'is_enabled')) {
                $table->boolean('is_enabled')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('feature_flags', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('is_enabled');
            }
            if (! Schema::hasColumn('feature_flags', 'start_date')) {
                $table->timestamp('start_date')->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('feature_flags', 'end_date')) {
                $table->timestamp('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('feature_flags', 'metadata')) {
                $table->json('metadata')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('feature_flags', 'priority')) {
                $table->string('priority')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('feature_flags', 'category')) {
                $table->string('category')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('feature_flags', 'impact_level')) {
                $table->string('impact_level')->nullable()->after('category');
            }
            if (! Schema::hasColumn('feature_flags', 'rollout_strategy')) {
                $table->string('rollout_strategy')->nullable()->after('impact_level');
            }
            if (! Schema::hasColumn('feature_flags', 'rollback_plan')) {
                $table->text('rollback_plan')->nullable()->after('rollout_strategy');
            }
            if (! Schema::hasColumn('feature_flags', 'success_metrics')) {
                $table->json('success_metrics')->nullable()->after('rollback_plan');
            }
            if (! Schema::hasColumn('feature_flags', 'approval_status')) {
                $table->string('approval_status')->nullable()->after('success_metrics');
            }
            if (! Schema::hasColumn('feature_flags', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approval_status');
            }
            if (! Schema::hasColumn('feature_flags', 'created_by')) {
                $table->string('created_by')->nullable()->after('approval_notes');
            }
            if (! Schema::hasColumn('feature_flags', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('feature_flags', 'last_activated')) {
                $table->timestamp('last_activated')->nullable()->after('updated_by');
            }
            if (! Schema::hasColumn('feature_flags', 'last_deactivated')) {
                $table->timestamp('last_deactivated')->nullable()->after('last_activated');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropColumn([
                'is_enabled',
                'is_global',
                'start_date',
                'end_date',
                'metadata',
                'priority',
                'category',
                'impact_level',
                'rollout_strategy',
                'rollback_plan',
                'success_metrics',
                'approval_status',
                'approval_notes',
                'created_by',
                'updated_by',
                'last_activated',
                'last_deactivated',
            ]);
        });
    }
};
