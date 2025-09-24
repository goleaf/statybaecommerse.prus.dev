<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            // Basic fields
            if (! Schema::hasColumn('channels', 'code')) {
                $table->string('code')->default('')->after('slug');
            }
            if (! Schema::hasColumn('channels', 'type')) {
                $table->string('type')->default('web')->after('code');
            }
            if (! Schema::hasColumn('channels', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_enabled');
            }
            if (! Schema::hasColumn('channels', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }

            // Configuration fields
            if (! Schema::hasColumn('channels', 'configuration')) {
                $table->json('configuration')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('channels', 'domain')) {
                $table->string('domain')->nullable()->after('configuration');
            }
            if (! Schema::hasColumn('channels', 'ssl_enabled')) {
                $table->boolean('ssl_enabled')->default(false)->after('domain');
            }

            // Meta information
            if (! Schema::hasColumn('channels', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('ssl_enabled');
            }
            if (! Schema::hasColumn('channels', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('channels', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            }

            // Analytics settings
            if (! Schema::hasColumn('channels', 'analytics_tracking_id')) {
                $table->string('analytics_tracking_id')->nullable()->after('meta_keywords');
            }
            if (! Schema::hasColumn('channels', 'analytics_enabled')) {
                $table->boolean('analytics_enabled')->default(false)->after('analytics_tracking_id');
            }

            // Payment settings
            if (! Schema::hasColumn('channels', 'payment_methods')) {
                $table->json('payment_methods')->nullable()->after('analytics_enabled');
            }
            if (! Schema::hasColumn('channels', 'default_payment_method')) {
                $table->string('default_payment_method')->nullable()->after('payment_methods');
            }

            // Shipping settings
            if (! Schema::hasColumn('channels', 'shipping_methods')) {
                $table->json('shipping_methods')->nullable()->after('default_payment_method');
            }
            if (! Schema::hasColumn('channels', 'default_shipping_method')) {
                $table->string('default_shipping_method')->nullable()->after('shipping_methods');
            }
            if (! Schema::hasColumn('channels', 'free_shipping_threshold')) {
                $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('default_shipping_method');
            }

            // Currency settings
            if (! Schema::hasColumn('channels', 'currency_code')) {
                $table->string('currency_code', 3)->default('EUR')->after('free_shipping_threshold');
            }
            if (! Schema::hasColumn('channels', 'currency_symbol')) {
                $table->string('currency_symbol')->default('€')->after('currency_code');
            }
            if (! Schema::hasColumn('channels', 'currency_position')) {
                $table->string('currency_position')->default('after')->after('currency_symbol');
            }

            // Language settings
            if (! Schema::hasColumn('channels', 'default_language')) {
                $table->string('default_language', 5)->default('lt')->after('currency_position');
            }
            if (! Schema::hasColumn('channels', 'supported_languages')) {
                $table->json('supported_languages')->nullable()->after('default_language');
            }

            // Contact information
            if (! Schema::hasColumn('channels', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('supported_languages');
            }
            if (! Schema::hasColumn('channels', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_email');
            }
            if (! Schema::hasColumn('channels', 'contact_address')) {
                $table->text('contact_address')->nullable()->after('contact_phone');
            }

            // Social media
            if (! Schema::hasColumn('channels', 'social_media')) {
                $table->json('social_media')->nullable()->after('contact_address');
            }

            // Legal documents
            if (! Schema::hasColumn('channels', 'legal_documents')) {
                $table->json('legal_documents')->nullable()->after('social_media');
            }
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $columns = [
                'code', 'type', 'is_active', 'sort_order', 'configuration', 'domain', 'ssl_enabled',
                'meta_title', 'meta_description', 'meta_keywords', 'analytics_tracking_id', 'analytics_enabled',
                'payment_methods', 'default_payment_method', 'shipping_methods', 'default_shipping_method',
                'free_shipping_threshold', 'currency_code', 'currency_symbol', 'currency_position',
                'default_language', 'supported_languages', 'contact_email', 'contact_phone', 'contact_address',
                'social_media', 'legal_documents',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('channels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
