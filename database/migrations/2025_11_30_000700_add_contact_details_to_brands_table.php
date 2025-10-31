<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brands')) {
            // Abort when the brands table is missing so legacy installs do not error during upgrades.
            return;
        }

        Schema::table('brands', function (Blueprint $table): void {
            if (! Schema::hasColumn('brands', 'contact_email')) {
                // Persist a dedicated contact email so seeders and admin forms can expose brand representatives.
                $table->string('contact_email')->nullable()->after('website');
            }

            if (! Schema::hasColumn('brands', 'contact_phone')) {
                // Store an optional phone number for each brand to support quick customer hand-offs.
                $table->string('contact_phone')->nullable()->after('contact_email');
            }

            if (! Schema::hasColumn('brands', 'sort_order')) {
                // Track manual ordering values for curated listings in storefront widgets and admin tables.
                $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
                $table->index('sort_order');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('brands')) {
            // Nothing to revert when the table does not exist.
            return;
        }

        Schema::table('brands', function (Blueprint $table): void {
            if (Schema::hasColumn('brands', 'sort_order')) {
                // Drop the sort ordering metadata during rollback for schema parity with legacy backups.
                $table->dropIndex('brands_sort_order_index');
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('brands', 'contact_phone')) {
                // Remove the contact phone column to restore the prior structure.
                $table->dropColumn('contact_phone');
            }

            if (Schema::hasColumn('brands', 'contact_email')) {
                // Remove the contact email column so older migrations stay consistent after rollback.
                $table->dropColumn('contact_email');
            }
        });
    }
};
