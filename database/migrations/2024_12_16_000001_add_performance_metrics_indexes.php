<?php

declare(strict_types=1);

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
        // Guard against fresh installs where the table is introduced in a later migration.
        if (! Schema::hasTable('performance_metrics')) {
            return;
        }

        Schema::table('performance_metrics', function (Blueprint $table) {
            // Composite index for route-based queries with date filtering
            $table->index(['page_route', 'created_at'], 'performance_metrics_route_date_idx');

            // Index for environment filtering
            $table->index('environment', 'performance_metrics_environment_idx');

            // Composite index for aggregation queries
            $table->index(['page_route', 'environment', 'created_at'], 'performance_metrics_aggregation_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip index drops when the table never existed in the current migration path.
        if (! Schema::hasTable('performance_metrics')) {
            return;
        }

        Schema::table('performance_metrics', function (Blueprint $table) {
            $table->dropIndex('performance_metrics_route_date_idx');
            $table->dropIndex('performance_metrics_environment_idx');
            $table->dropIndex('performance_metrics_aggregation_idx');
        });
    }
};
