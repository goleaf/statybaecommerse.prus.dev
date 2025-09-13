<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Add new fields for enhanced address functionality
            $table->string('email')->nullable()->after('phone');
            $table->boolean('is_billing')->default(false)->after('is_default');
            $table->boolean('is_shipping')->default(false)->after('is_billing');
            $table->json('notes')->nullable()->after('is_shipping');
            $table->string('apartment')->nullable()->after('notes');
            $table->string('floor')->nullable()->after('apartment');
            $table->string('building')->nullable()->after('floor');
            $table->string('landmark')->nullable()->after('building');
            $table->json('instructions')->nullable()->after('landmark');
            $table->string('company_name')->nullable()->after('instructions');
            $table->string('company_vat')->nullable()->after('company_name');
            $table->boolean('is_active')->default(true)->after('company_vat');

            // Add indexes for better performance (skip user_id_is_default as it already exists)
            $table->index(['user_id', 'is_billing']);
            $table->index(['user_id', 'is_shipping']);
            $table->index(['user_id', 'is_active']);
            $table->index(['country_code', 'city']);
            $table->index(['postal_code']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_billing']);
            $table->dropIndex(['user_id', 'is_shipping']);
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['country_code', 'city']);
            $table->dropIndex(['postal_code']);
            $table->dropIndex(['type']);

            $table->dropColumn([
                'email',
                'is_billing',
                'is_shipping',
                'notes',
                'apartment',
                'floor',
                'building',
                'landmark',
                'instructions',
                'company_name',
                'company_vat',
                'is_active',
            ]);
        });
    }
};