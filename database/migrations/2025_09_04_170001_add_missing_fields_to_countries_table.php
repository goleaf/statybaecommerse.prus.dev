<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Add missing fields for the Country model
            if (!Schema::hasColumn('countries', 'code')) {
                $table->string('code', 3)->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('countries', 'iso_code')) {
                $table->string('iso_code', 3)->unique()->nullable()->after('code');
            }
            // phone_code and currency_code already exist in the original table
            if (!Schema::hasColumn('countries', 'currency_symbol')) {
                $table->string('currency_symbol', 5)->nullable()->after('currency_code');
            }
            if (!Schema::hasColumn('countries', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('currency_symbol');
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
            if (!Schema::hasColumn('countries', 'metadata')) {
                $table->json('metadata')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('countries', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('metadata');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'iso_code',
                'currency_symbol',
                'is_active',
                'is_eu_member',
                'requires_vat',
                'vat_rate',
                'timezone',
                'metadata',
                'sort_order',
            ]);
        });
    }
};
