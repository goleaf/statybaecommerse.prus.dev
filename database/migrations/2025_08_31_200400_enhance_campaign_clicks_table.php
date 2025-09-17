<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('campaign_clicks')) {
            Schema::table('campaign_clicks', function (Blueprint $table) {
                $stringCols = ['referer', 'device_type', 'browser', 'os', 'country', 'city', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
                foreach ($stringCols as $col) {
                    if (!Schema::hasColumn('campaign_clicks', $col)) {
                        $table->string($col)->nullable();
                    }
                }
                if (!Schema::hasColumn('campaign_clicks', 'conversion_value')) {
                    $table->decimal('conversion_value', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('campaign_clicks', 'is_converted')) {
                    $table->boolean('is_converted')->default(false);
                }
                if (!Schema::hasColumn('campaign_clicks', 'conversion_data')) {
                    $table->json('conversion_data')->nullable();
                }

                foreach ([['device_type'], ['browser'], ['country'], ['utm_source'], ['is_converted'], ['clicked_at', 'campaign_id']] as $idx) {
                    try {
                        $table->index($idx);
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('campaign_clicks') && !Schema::hasTable('campaign_click_translations')) {
            Schema::create('campaign_click_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_click_id');
                $table->string('locale', 5);
                $table->string('click_type_label')->nullable();
                $table->string('device_type_label')->nullable();
                $table->string('browser_label')->nullable();
                $table->string('os_label')->nullable();
                $table->text('notes')->nullable();
                $table->json('custom_data')->nullable();
                $table->timestamps();

                $table->unique(['campaign_click_id', 'locale'], 'cct_click_locale_unique');
                $table->index(['locale']);
                $table->foreign('campaign_click_id')->references('id')->on('campaign_clicks')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('campaign_conversions') && !Schema::hasTable('campaign_conversion_translations')) {
            Schema::create('campaign_conversion_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_conversion_id');
                $table->string('locale', 5);
                $table->string('conversion_type_label')->nullable();
                $table->string('status_label')->nullable();
                $table->text('notes')->nullable();
                $table->json('custom_data')->nullable();
                $table->timestamps();

                $table->unique(['campaign_conversion_id', 'locale'], 'cct_conversion_locale_unique');
                $table->index(['locale']);
                $table->foreign('campaign_conversion_id')->references('id')->on('campaign_conversions')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_conversion_translations');
        Schema::dropIfExists('campaign_click_translations');

        if (Schema::hasTable('campaign_clicks')) {
            Schema::table('campaign_clicks', function (Blueprint $table) {
                foreach ([['device_type'], ['browser'], ['country'], ['utm_source'], ['is_converted'], ['clicked_at', 'campaign_id']] as $idx) {
                    try {
                        $table->dropIndex($idx);
                    } catch (\Throwable $e) {
                    }
                }

                $columns = [
                    'referer',
                    'device_type',
                    'browser',
                    'os',
                    'country',
                    'city',
                    'utm_source',
                    'utm_medium',
                    'utm_campaign',
                    'utm_term',
                    'utm_content',
                    'conversion_value',
                    'is_converted',
                    'conversion_data',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('campaign_clicks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
