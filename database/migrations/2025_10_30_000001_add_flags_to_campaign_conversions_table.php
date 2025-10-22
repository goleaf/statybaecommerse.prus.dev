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
        if (! Schema::hasTable('campaign_conversions')) {
            return;
        }

        Schema::table('campaign_conversions', function (Blueprint $table): void {
            // Ensure verification flags exist before the analytics columns so filters work.
            if (! Schema::hasColumn('campaign_conversions', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('city');
            }

            if (! Schema::hasColumn('campaign_conversions', 'is_attributed')) {
                $table->boolean('is_attributed')->default(false)->after('is_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('campaign_conversions')) {
            return;
        }

        Schema::table('campaign_conversions', function (Blueprint $table): void {
            // Drop verification flags only if they were previously added by this migration.
            if (Schema::hasColumn('campaign_conversions', 'is_attributed')) {
                $table->dropColumn('is_attributed');
            }

            if (Schema::hasColumn('campaign_conversions', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
        });
    }
};
