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
        if (! Schema::hasTable('campaign_conversions')) {
            return;
        }

        Schema::table('campaign_conversions', function (Blueprint $table): void {
            if (! Schema::hasColumn('campaign_conversions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('campaign_conversions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
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
            if (Schema::hasColumn('campaign_conversions', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            if (Schema::hasColumn('campaign_conversions', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
