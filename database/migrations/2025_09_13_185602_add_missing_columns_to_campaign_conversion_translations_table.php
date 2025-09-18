<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_conversion_translations')) {
            return;
        }

        Schema::table('campaign_conversion_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_conversion_translations', 'created_at')) {
                $table->timestamps();
            }
            if (!Schema::hasColumn('campaign_conversion_translations', 'custom_attributes')) {
                $table->json('custom_attributes')->nullable()->after('custom_data');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_conversion_translations')) {
            return;
        }

        Schema::table('campaign_conversion_translations', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_conversion_translations', 'created_at')) {
                $table->dropTimestamps();
            }
            if (Schema::hasColumn('campaign_conversion_translations', 'custom_attributes')) {
                $table->dropColumn('custom_attributes');
            }
        });
    }
};
