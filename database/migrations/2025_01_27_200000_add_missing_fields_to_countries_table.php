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
        Schema::table('countries', function (Blueprint $table) {
            // Add missing fields for the Country model
            $table->string('code', 3)->unique()->nullable()->after('id');
            $table->string('iso_code', 3)->unique()->nullable()->after('code');
            $table->string('phone_code', 10)->nullable()->after('iso_code');
            $table->string('currency_code', 3)->nullable()->after('phone_code');
            $table->string('currency_symbol', 5)->nullable()->after('currency_code');
            $table->boolean('is_active')->default(true)->after('currency_symbol');
            $table->boolean('is_eu_member')->default(false)->after('is_active');
            $table->boolean('requires_vat')->default(false)->after('is_eu_member');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('requires_vat');
            $table->string('timezone', 50)->nullable()->after('vat_rate');
            $table->json('metadata')->nullable()->after('timezone');
            $table->integer('sort_order')->default(0)->after('metadata');
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
                'phone_code',
                'currency_code',
                'currency_symbol',
                'is_active',
                'is_eu_member',
                'requires_vat',
                'vat_rate',
                'timezone',
                'metadata',
                'sort_order'
            ]);
        });
    }
};

