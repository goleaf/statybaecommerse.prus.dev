<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('countries', 'name_official')) {
                $table->string('name_official')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('countries', 'code')) {
                $table->string('code', 3)->nullable()->unique()->after('ccn3');
            }
            
            if (!Schema::hasColumn('countries', 'iso_code')) {
                $table->string('iso_code', 3)->nullable()->unique()->after('code');
            }
            
            if (!Schema::hasColumn('countries', 'currency_symbol')) {
                $table->string('currency_symbol', 5)->nullable()->after('currency_code');
            }
            
            if (!Schema::hasColumn('countries', 'phone_calling_code')) {
                $table->string('phone_calling_code', 10)->nullable()->after('phone_code');
            }
            
            if (!Schema::hasColumn('countries', 'svg_flag')) {
                $table->string('svg_flag')->nullable()->after('flag');
            }
            
            if (!Schema::hasColumn('countries', 'region')) {
                $table->string('region')->nullable()->after('svg_flag');
            }
            
            if (!Schema::hasColumn('countries', 'subregion')) {
                $table->string('subregion')->nullable()->after('region');
            }
            
            if (!Schema::hasColumn('countries', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('subregion');
            }
            
            if (!Schema::hasColumn('countries', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            
            if (!Schema::hasColumn('countries', 'currencies')) {
                $table->json('currencies')->nullable()->after('longitude');
            }
            
            if (!Schema::hasColumn('countries', 'languages')) {
                $table->json('languages')->nullable()->after('currencies');
            }
            
            if (!Schema::hasColumn('countries', 'timezones')) {
                $table->json('timezones')->nullable()->after('languages');
            }
            
            if (!Schema::hasColumn('countries', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('timezones');
            }
            
            if (!Schema::hasColumn('countries', 'is_eu_member')) {
                $table->boolean('is_eu_member')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('countries', 'requires_vat')) {
                $table->boolean('requires_vat')->default(false)->after('is_eu_member');
            }
            
            if (!Schema::hasColumn('countries', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->nullable()->after('requires_vat');
            }
            
            if (!Schema::hasColumn('countries', 'timezone')) {
                $table->string('timezone', 50)->nullable()->after('vat_rate');
            }
            
            if (!Schema::hasColumn('countries', 'description')) {
                $table->text('description')->nullable()->after('timezone');
            }
            
            if (!Schema::hasColumn('countries', 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('countries', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('metadata');
            }

            // Make name nullable since we'll use translations
            if (Schema::hasColumn('countries', 'name')) {
                $table->string('name')->nullable()->change();
            }
        });

        // Add indexes for better performance
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasIndex('countries', 'countries_is_active_index')) {
                $table->index('is_active');
            }
            
            if (!Schema::hasIndex('countries', 'countries_is_enabled_index')) {
                $table->index('is_enabled');
            }
            
            if (!Schema::hasIndex('countries', 'countries_is_eu_member_index')) {
                $table->index('is_eu_member');
            }
            
            if (!Schema::hasIndex('countries', 'countries_requires_vat_index')) {
                $table->index('requires_vat');
            }
            
            if (!Schema::hasIndex('countries', 'countries_region_index')) {
                $table->index('region');
            }
            
            if (!Schema::hasIndex('countries', 'countries_currency_code_index')) {
                $table->index('currency_code');
            }
            
            if (!Schema::hasIndex('countries', 'countries_sort_order_index')) {
                $table->index('sort_order');
            }
            
            if (!Schema::hasIndex('countries', 'countries_active_enabled_sort_index')) {
                $table->index(['is_active', 'is_enabled', 'sort_order']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('countries_is_active_index');
            $table->dropIndex('countries_is_enabled_index');
            $table->dropIndex('countries_is_eu_member_index');
            $table->dropIndex('countries_requires_vat_index');
            $table->dropIndex('countries_region_index');
            $table->dropIndex('countries_currency_code_index');
            $table->dropIndex('countries_sort_order_index');
            $table->dropIndex('countries_active_enabled_sort_index');

            // Drop columns
            $table->dropColumn([
                'name_official',
                'code',
                'iso_code',
                'currency_symbol',
                'phone_calling_code',
                'svg_flag',
                'region',
                'subregion',
                'latitude',
                'longitude',
                'currencies',
                'languages',
                'timezones',
                'is_active',
                'is_eu_member',
                'requires_vat',
                'vat_rate',
                'timezone',
                'description',
                'metadata',
                'sort_order',
            ]);

            // Make name required again
            $table->string('name')->nullable(false)->change();
        });
    }
};
