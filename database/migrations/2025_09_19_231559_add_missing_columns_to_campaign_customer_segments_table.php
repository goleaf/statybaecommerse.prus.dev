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
        Schema::table('campaign_customer_segments', function (Blueprint $table) {
            $table->json('targeting_tags')->nullable()->after('segment_criteria');
            $table->text('custom_conditions')->nullable()->after('targeting_tags');
            $table->boolean('track_performance')->default(true)->after('custom_conditions');
            $table->boolean('auto_optimize')->default(false)->after('track_performance');
            $table->boolean('is_active')->default(true)->after('auto_optimize');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_customer_segments', function (Blueprint $table) {
            $table->dropColumn([
                'targeting_tags',
                'custom_conditions',
                'track_performance',
                'auto_optimize',
                'is_active',
                'sort_order',
                'deleted_at',
            ]);
        });
    }
};
