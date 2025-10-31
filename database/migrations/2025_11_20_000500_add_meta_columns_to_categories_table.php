<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            // Ensure SEO metadata fields exist so enhanced seeders can hydrate deterministic content.
            if (! Schema::hasColumn('categories', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('seo_description');
            }

            if (! Schema::hasColumn('categories', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }

            // Provide a nullable icon column to support Filament category badges.
            if (! Schema::hasColumn('categories', 'icon')) {
                $table->string('icon')->nullable()->after('color');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            // Drop the SEO and icon helpers if the migration is rolled back.
            if (Schema::hasColumn('categories', 'meta_description')) {
                $table->dropColumn('meta_description');
            }

            if (Schema::hasColumn('categories', 'meta_title')) {
                $table->dropColumn('meta_title');
            }

            if (Schema::hasColumn('categories', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
