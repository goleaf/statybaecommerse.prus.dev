<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('campaign_conversions')) {
            Schema::table('campaign_conversions', function (Blueprint $table) {
                // New fields for enhanced tracking
                $columnsString = [
                    'status' => ['type' => 'string', 'default' => 'completed', 'after' => 'conversion_data'],
                    'source' => ['type' => 'string', 'nullable' => true, 'after' => 'status'],
                    'medium' => ['type' => 'string', 'nullable' => true, 'after' => 'source'],
                    'campaign_name' => ['type' => 'string', 'nullable' => true, 'after' => 'medium'],
                    'utm_content' => ['type' => 'string', 'nullable' => true, 'after' => 'campaign_name'],
                    'utm_term' => ['type' => 'string', 'nullable' => true, 'after' => 'utm_content'],
                    'ip_address' => ['type' => 'string', 'nullable' => true, 'after' => 'referrer'],
                    'device_type' => ['type' => 'string', 'nullable' => true, 'after' => 'user_agent'],
                    'browser' => ['type' => 'string', 'nullable' => true, 'after' => 'device_type'],
                    'os' => ['type' => 'string', 'nullable' => true, 'after' => 'browser'],
                    'country' => ['type' => 'string', 'nullable' => true, 'after' => 'os'],
                    'city' => ['type' => 'string', 'nullable' => true, 'after' => 'country'],
                    'exit_page' => ['type' => 'string', 'nullable' => true, 'after' => 'bounce_rate'],
                    'landing_page' => ['type' => 'string', 'nullable' => true, 'after' => 'exit_page'],
                    'funnel_step' => ['type' => 'string', 'nullable' => true, 'after' => 'landing_page'],
                    'attribution_model' => ['type' => 'string', 'default' => 'last_click', 'after' => 'funnel_step'],
                ];
                foreach ($columnsString as $name => $cfg) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $col = $table->string($name);
                        if (($cfg['nullable'] ?? false) === true) {
                            $col->nullable();
                        }
                    }
                }

                $columnsText = ['referrer', 'user_agent', 'notes'];
                foreach ($columnsText as $name) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $table->text($name)->nullable();
                    }
                }

                $columnsBool = ['is_mobile', 'is_tablet', 'is_desktop'];
                foreach ($columnsBool as $name) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $table->boolean($name)->default(false);
                    }
                }

                $columnsInt = ['conversion_duration', 'page_views', 'time_on_site', 'conversion_window', 'lookback_window', 'assisted_conversions', 'total_conversions', 'payback_period'];
                foreach ($columnsInt as $name) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $table->integer($name)->nullable();
                    }
                }

                $columnsDecimal = [
                    'bounce_rate' => [5, 2],
                    'last_click_attribution' => [15, 2],
                    'first_click_attribution' => [15, 2],
                    'linear_attribution' => [15, 2],
                    'time_decay_attribution' => [15, 2],
                    'position_based_attribution' => [15, 2],
                    'data_driven_attribution' => [15, 2],
                    'assisted_conversion_value' => [15, 2],
                    'total_conversion_value' => [15, 2],
                    'conversion_rate' => [8, 4],
                    'cost_per_conversion' => [15, 2],
                    'roi' => [8, 4],
                    'roas' => [8, 4],
                    'lifetime_value' => [15, 2],
                    'customer_acquisition_cost' => [15, 2],
                ];
                foreach ($columnsDecimal as $name => [$precision, $scale]) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $table->decimal($name, $precision, $scale)->nullable();
                    }
                }

                $columnsJson = ['conversion_path', 'touchpoints', 'tags', 'custom_attributes'];
                foreach ($columnsJson as $name) {
                    if (!Schema::hasColumn('campaign_conversions', $name)) {
                        $table->json($name)->nullable();
                    }
                }
            });

            // indexes
            Schema::table('campaign_conversions', function (Blueprint $table) {
                foreach ([
                    ['status'],
                    ['source'],
                    ['medium'],
                    ['device_type'],
                    ['country'],
                    ['conversion_type', 'status'],
                    ['converted_at', 'status'],
                    ['campaign_id', 'conversion_type'],
                    ['customer_id', 'converted_at'],
                ] as $index) {
                    try {
                        $table->index($index);
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('campaign_conversions') && !Schema::hasTable('campaign_conversion_translations')) {
            Schema::create('campaign_conversion_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_conversion_id')->constrained('campaign_conversions')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('locale', 5);
                $table->text('notes')->nullable();
                $table->json('custom_attributes')->nullable();
                $table->timestamps();

                $table->unique(['campaign_conversion_id', 'locale']);
                $table->index(['locale']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_conversion_translations');

        if (Schema::hasTable('campaign_conversions')) {
            Schema::table('campaign_conversions', function (Blueprint $table) {
                foreach ([
                    ['status'],
                    ['source'],
                    ['medium'],
                    ['device_type'],
                    ['country'],
                    ['conversion_type', 'status'],
                    ['converted_at', 'status'],
                    ['campaign_id', 'conversion_type'],
                    ['customer_id', 'converted_at'],
                ] as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (\Throwable $e) {
                    }
                }

                $columns = [
                    'status',
                    'source',
                    'medium',
                    'campaign_name',
                    'utm_content',
                    'utm_term',
                    'referrer',
                    'ip_address',
                    'user_agent',
                    'device_type',
                    'browser',
                    'os',
                    'country',
                    'city',
                    'is_mobile',
                    'is_tablet',
                    'is_desktop',
                    'conversion_duration',
                    'page_views',
                    'time_on_site',
                    'bounce_rate',
                    'exit_page',
                    'landing_page',
                    'funnel_step',
                    'attribution_model',
                    'conversion_path',
                    'touchpoints',
                    'last_click_attribution',
                    'first_click_attribution',
                    'linear_attribution',
                    'time_decay_attribution',
                    'position_based_attribution',
                    'data_driven_attribution',
                    'conversion_window',
                    'lookback_window',
                    'assisted_conversions',
                    'assisted_conversion_value',
                    'total_conversions',
                    'total_conversion_value',
                    'conversion_rate',
                    'cost_per_conversion',
                    'roi',
                    'roas',
                    'lifetime_value',
                    'customer_acquisition_cost',
                    'payback_period',
                    'notes',
                    'tags',
                    'custom_attributes',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('campaign_conversions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
