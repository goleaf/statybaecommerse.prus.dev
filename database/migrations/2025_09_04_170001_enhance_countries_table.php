<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            return;
        }

        Schema::table('countries', function (Blueprint $table) {
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

            try {
                $table->string('name')->nullable()->change();
            } catch (\Throwable $e) {
            }
        });

        Schema::table('countries', function (Blueprint $table) {
            try {
                $table->index('is_active');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('is_enabled');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('is_eu_member');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('requires_vat');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('region');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('currency_code');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('sort_order');
            } catch (\Throwable $e) {
            }
            try {
                $table->index(['is_active', 'is_enabled', 'sort_order']);
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('countries')) {
            return;
        }

        Schema::table('countries', function (Blueprint $table) {
            // Drop indexes first
            try {
                $table->dropIndex('countries_is_active_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_is_enabled_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_is_eu_member_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_requires_vat_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_region_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_currency_code_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_sort_order_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('countries_active_enabled_sort_index');
            } catch (\Throwable $e) {
            }

            // Drop columns
            foreach ([
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
            ] as $col) {
                if (Schema::hasColumn('countries', $col)) {
                    $table->dropColumn($col);
                }
            }

            try {
                $table->string('name')->nullable(false)->change();
            } catch (\Throwable $e) {
            }
        });
    }
};
