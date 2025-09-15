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
        Schema::table('recommendation_configs', function (Blueprint $table) {
            $table->integer('cache_ttl')->default(3600)->after('min_score');
            $table->boolean('enable_caching')->default(true)->after('cache_ttl');
            $table->boolean('enable_analytics')->default(true)->after('enable_caching');
            $table->integer('batch_size')->default(100)->after('enable_analytics');
            $table->integer('timeout_seconds')->default(30)->after('batch_size');
            $table->json('conditions')->nullable()->after('timeout_seconds');
            $table->text('notes')->nullable()->after('conditions');
            $table->json('metadata')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendation_configs', function (Blueprint $table) {
            $table->dropColumn([
                'cache_ttl',
                'enable_caching',
                'enable_analytics',
                'batch_size',
                'timeout_seconds',
                'conditions',
                'notes',
                'metadata',
            ]);
        });
    }
};
