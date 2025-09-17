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
            $table->boolean('is_enabled')->default(false)->after('is_active');
            $table->boolean('is_global')->default(false)->after('is_enabled');
            $table->timestamp('start_date')->nullable()->after('ends_at');
            $table->timestamp('end_date')->nullable()->after('start_date');
            $table->json('metadata')->nullable()->after('end_date');
            $table->string('priority')->nullable()->after('metadata');
            $table->string('category')->nullable()->after('priority');
            $table->string('impact_level')->nullable()->after('category');
            $table->string('rollout_strategy')->nullable()->after('impact_level');
            $table->text('rollback_plan')->nullable()->after('rollout_strategy');
            $table->json('success_metrics')->nullable()->after('rollback_plan');
            $table->string('approval_status')->nullable()->after('success_metrics');
            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->string('created_by')->nullable()->after('approval_notes');
            $table->string('updated_by')->nullable()->after('created_by');
            $table->timestamp('last_activated')->nullable()->after('updated_by');
            $table->timestamp('last_deactivated')->nullable()->after('last_activated');
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