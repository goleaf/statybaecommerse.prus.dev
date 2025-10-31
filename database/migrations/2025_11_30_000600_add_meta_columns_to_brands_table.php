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
            // Bail out early if the brands table does not exist yet.
            return;
        }

        Schema::table('brands', function (Blueprint $table): void {
            if (! Schema::hasColumn('brands', 'meta_title')) {
                // Store localized SEO titles for brand landing pages.
                $table->string('meta_title')->nullable()->after('website');
            }

            if (! Schema::hasColumn('brands', 'meta_description')) {
                // Persist SEO descriptions alongside brand metadata.
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('brands')) {
            // Nothing to revert when the brands table is missing.
            return;
        }

        Schema::table('brands', function (Blueprint $table): void {
            if (Schema::hasColumn('brands', 'meta_description')) {
                // Remove SEO descriptions during rollback to restore the original schema.
                $table->dropColumn('meta_description');
            }

            if (Schema::hasColumn('brands', 'meta_title')) {
                // Remove SEO titles during rollback to keep parity with legacy installs.
                $table->dropColumn('meta_title');
            }
        });
    }
};
