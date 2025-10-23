<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Build the customers table that backs order history, analytics and Filament resources.
     */
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            return;
        }

        $countriesAvailable = Schema::hasTable('countries');
        $citiesAvailable = Schema::hasTable('cities');
        $companiesAvailable = Schema::hasTable('companies');

        Schema::create('customers', function (Blueprint $table) use ($countriesAvailable, $citiesAvailable, $companiesAvailable): void {
            // Primary identifier and personal contact details.
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();

            // Optional location references so analytics can aggregate by geography.
            if ($countriesAvailable) {
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('country_id')->nullable();
            }

            if ($citiesAvailable) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('city_id')->nullable();
            }

            // Associate customers with companies for B2B flows.
            if ($companiesAvailable) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('company_id')->nullable();
            }

            // Operational metadata used by filters, automation and auditing.
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Keep frequent lookups performant for status dashboards and search helpers.
            $table->index(['is_active']);
            $table->index(['country_id']);
            $table->index(['city_id']);
            $table->index(['company_id']);
        });
    }

    /**
     * Drop the customers table when rolling the migration back.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
