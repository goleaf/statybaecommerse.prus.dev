<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_clicks', function (Blueprint $table) {
            // Add new tracking fields
            if (! Schema::hasColumn('campaign_clicks', 'referer')) {
                $table->string('referer')->nullable()->after('user_agent');
            }
            if (! Schema::hasColumn('campaign_clicks', 'device_type')) {
                $table->string('device_type')->nullable()->after('referer');
            }
            if (! Schema::hasColumn('campaign_clicks', 'browser')) {
                $table->string('browser')->nullable()->after('device_type');
            }
            if (! Schema::hasColumn('campaign_clicks', 'os')) {
                $table->string('os')->nullable()->after('browser');
            }
            if (! Schema::hasColumn('campaign_clicks', 'country')) {
                $table->string('country')->nullable()->after('os');
            }
            if (! Schema::hasColumn('campaign_clicks', 'city')) {
                $table->string('city')->nullable()->after('country');
            }

            // UTM parameters
            if (! Schema::hasColumn('campaign_clicks', 'utm_source')) {
                $table->string('utm_source')->nullable()->after('city');
            }
            if (! Schema::hasColumn('campaign_clicks', 'utm_medium')) {
                $table->string('utm_medium')->nullable()->after('utm_source');
            }
            if (! Schema::hasColumn('campaign_clicks', 'utm_campaign')) {
                $table->string('utm_campaign')->nullable()->after('utm_medium');
            }
            if (! Schema::hasColumn('campaign_clicks', 'utm_term')) {
                $table->string('utm_term')->nullable()->after('utm_campaign');
            }
            if (! Schema::hasColumn('campaign_clicks', 'utm_content')) {
                $table->string('utm_content')->nullable()->after('utm_term');
            }

            // Conversion tracking
            if (! Schema::hasColumn('campaign_clicks', 'conversion_value')) {
                $table->decimal('conversion_value', 12, 2)->default(0)->after('utm_content');
            }
            if (! Schema::hasColumn('campaign_clicks', 'is_converted')) {
                $table->boolean('is_converted')->default(false)->after('conversion_value');
            }
            if (! Schema::hasColumn('campaign_clicks', 'conversion_data')) {
                $table->json('conversion_data')->nullable()->after('is_converted');
            }

            // Add indexes for performance
            $table->index(['device_type']);
            $table->index(['browser']);
            $table->index(['country']);
            $table->index(['utm_source']);
            $table->index(['is_converted']);
            $table->index(['clicked_at', 'campaign_id']);
        });

        // Create campaign click translations table
        if (! Schema::hasTable('campaign_click_translations')) {
            Schema::create('campaign_click_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_click_id')->constrained('campaign_clicks')->cascadeOnDelete();
                $table->string('locale', 5);
                $table->string('click_type_label')->nullable();
                $table->string('device_type_label')->nullable();
                $table->string('browser_label')->nullable();
                $table->string('os_label')->nullable();
                $table->text('notes')->nullable();
                $table->json('custom_data')->nullable();

                $table->unique(['campaign_click_id', 'locale']);
                $table->index(['locale']);
            });
        }

        // Create campaign conversion translations table
        if (! Schema::hasTable('campaign_conversion_translations')) {
            Schema::create('campaign_conversion_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_conversion_id')->constrained('campaign_conversions')->cascadeOnDelete();
                $table->string('locale', 5);
                $table->string('conversion_type_label')->nullable();
                $table->string('status_label')->nullable();
                $table->text('notes')->nullable();
                $table->json('custom_data')->nullable();

                $table->unique(['campaign_conversion_id', 'locale']);
                $table->index(['locale']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_conversion_translations');
        Schema::dropIfExists('campaign_click_translations');

        Schema::table('campaign_clicks', function (Blueprint $table) {
            $columns = [
                'referer', 'device_type', 'browser', 'os', 'country', 'city',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'conversion_value', 'is_converted', 'conversion_data',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('campaign_clicks', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop indexes
            try {
                $table->dropIndex(['device_type']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['browser']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['country']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['utm_source']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['is_converted']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['clicked_at', 'campaign_id']);
            } catch (\Throwable $e) {
            }
        });
    }
};
