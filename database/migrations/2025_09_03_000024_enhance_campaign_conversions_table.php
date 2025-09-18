<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_conversions')) {
            return;
        }

        Schema::table('campaign_conversions', function (Blueprint $table) {
            // Add new fields for enhanced tracking
            $table->string('status')->default('completed')->after('conversion_data');
            $table->string('source')->nullable()->after('status');
            $table->string('medium')->nullable()->after('source');
            $table->string('campaign_name')->nullable()->after('medium');
            $table->string('utm_content')->nullable()->after('campaign_name');
            $table->string('utm_term')->nullable()->after('utm_content');
            $table->text('referrer')->nullable()->after('utm_term');
            $table->string('ip_address')->nullable()->after('referrer');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('device_type')->nullable()->after('user_agent');
            $table->string('browser')->nullable()->after('device_type');
            $table->string('os')->nullable()->after('browser');
            $table->string('country')->nullable()->after('os');
            $table->string('city')->nullable()->after('country');
            $table->boolean('is_mobile')->default(false)->after('city');
            $table->boolean('is_tablet')->default(false)->after('is_mobile');
            $table->boolean('is_desktop')->default(false)->after('is_tablet');

            // Conversion analytics
            $table->integer('conversion_duration')->nullable()->after('is_desktop');
            $table->integer('page_views')->nullable()->after('conversion_duration');
            $table->integer('time_on_site')->nullable()->after('page_views');
            $table->decimal('bounce_rate', 5, 2)->nullable()->after('time_on_site');
            $table->string('exit_page')->nullable()->after('bounce_rate');
            $table->string('landing_page')->nullable()->after('exit_page');
            $table->string('funnel_step')->nullable()->after('landing_page');

            // Attribution tracking
            $table->string('attribution_model')->default('last_click')->after('funnel_step');
            $table->json('conversion_path')->nullable()->after('attribution_model');
            $table->json('touchpoints')->nullable()->after('conversion_path');
            $table->decimal('last_click_attribution', 12, 2)->nullable()->after('touchpoints');
            $table->decimal('first_click_attribution', 12, 2)->nullable()->after('last_click_attribution');
            $table->decimal('linear_attribution', 12, 2)->nullable()->after('first_click_attribution');
            $table->decimal('time_decay_attribution', 12, 2)->nullable()->after('linear_attribution');
            $table->decimal('position_based_attribution', 12, 2)->nullable()->after('time_decay_attribution');
            $table->decimal('data_driven_attribution', 12, 2)->nullable()->after('position_based_attribution');

            // Conversion windows
            $table->integer('conversion_window')->default(30)->after('data_driven_attribution');
            $table->integer('lookback_window')->default(90)->after('conversion_window');

            // Assisted conversions
            $table->integer('assisted_conversions')->default(0)->after('lookback_window');
            $table->decimal('assisted_conversion_value', 12, 2)->default(0)->after('assisted_conversions');
            $table->integer('total_conversions')->default(1)->after('assisted_conversion_value');
            $table->decimal('total_conversion_value', 12, 2)->default(0)->after('total_conversions');

            // Performance metrics
            $table->decimal('conversion_rate', 8, 4)->nullable()->after('total_conversion_value');
            $table->decimal('cost_per_conversion', 12, 2)->nullable()->after('conversion_rate');
            $table->decimal('roi', 8, 4)->nullable()->after('cost_per_conversion');
            $table->decimal('roas', 8, 4)->nullable()->after('roi');
            $table->decimal('lifetime_value', 12, 2)->nullable()->after('roas');
            $table->decimal('customer_acquisition_cost', 12, 2)->nullable()->after('lifetime_value');
            $table->integer('payback_period')->nullable()->after('customer_acquisition_cost');

            // Additional fields
            $table->text('notes')->nullable()->after('payback_period');
            $table->json('tags')->nullable()->after('notes');
            $table->json('custom_attributes')->nullable()->after('tags');
        });

        // Create translations table
        if (! Schema::hasTable('campaign_conversion_translations')) {
            Schema::create('campaign_conversion_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_conversion_id')->constrained('campaign_conversions')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->unique(['campaign_conversion_id', 'locale']);
            $table->index(['locale']);
            });
        }

        // Add indexes for better performance
        Schema::table('campaign_conversions', function (Blueprint $table) {
            $table->index(['status']);
            $table->index(['source']);
            $table->index(['medium']);
            $table->index(['device_type']);
            $table->index(['country']);
            $table->index(['conversion_type', 'status']);
            $table->index(['converted_at', 'status']);
            $table->index(['campaign_id', 'conversion_type']);
            $table->index(['customer_id', 'converted_at']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_conversions')) {
            Schema::dropIfExists('campaign_conversion_translations');
            return;
        }

        Schema::dropIfExists('campaign_conversion_translations');

        Schema::table('campaign_conversions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['source']);
            $table->dropIndex(['medium']);
            $table->dropIndex(['device_type']);
            $table->dropIndex(['country']);
            $table->dropIndex(['conversion_type', 'status']);
            $table->dropIndex(['converted_at', 'status']);
            $table->dropIndex(['campaign_id', 'conversion_type']);
            $table->dropIndex(['customer_id', 'converted_at']);

            $table->dropColumn([
                'status', 'source', 'medium', 'campaign_name', 'utm_content', 'utm_term',
                'referrer', 'ip_address', 'user_agent', 'device_type', 'browser', 'os',
                'country', 'city', 'is_mobile', 'is_tablet', 'is_desktop',
                'conversion_duration', 'page_views', 'time_on_site', 'bounce_rate',
                'exit_page', 'landing_page', 'funnel_step', 'attribution_model',
                'conversion_path', 'touchpoints', 'last_click_attribution',
                'first_click_attribution', 'linear_attribution', 'time_decay_attribution',
                'position_based_attribution', 'data_driven_attribution',
                'conversion_window', 'lookback_window', 'assisted_conversions',
                'assisted_conversion_value', 'total_conversions', 'total_conversion_value',
                'conversion_rate', 'cost_per_conversion', 'roi', 'roas',
                'lifetime_value', 'customer_acquisition_cost', 'payback_period',
                'notes', 'tags', 'custom_attributes',
            ]);
        });
    }
};
