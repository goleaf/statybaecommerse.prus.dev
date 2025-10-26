<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            // Skip the migration when the audit table is intentionally omitted from lightweight installs.
            return;
        }

        Schema::table('stock_movements', static function (Blueprint $table): void {
            // Add the correlation identifier so movements can be deduplicated safely across services.
            if (! Schema::hasColumn('stock_movements', 'correlation_id')) {
                $table->uuid('correlation_id')->nullable()->after('reference');
                $table->index('correlation_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::table('stock_movements', static function (Blueprint $table): void {
            if (Schema::hasColumn('stock_movements', 'correlation_id')) {
                // Drop both the index and column to roll the schema back cleanly.
                $table->dropIndex(['correlation_id']);
                $table->dropColumn('correlation_id');
            }
        });
    }
};
