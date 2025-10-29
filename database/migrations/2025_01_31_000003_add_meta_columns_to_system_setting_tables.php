<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure the categories table exposes a JSON meta column for structured configuration payloads.
        if (! Schema::hasColumn('system_setting_categories', 'meta')) {
            Schema::table('system_setting_categories', function (Blueprint $table): void {
                $table->json('meta')->nullable()->after('metadata')->comment('Arbitrary structured metadata for category-specific UI tweaks');
            });
        }

        // Copy any legacy metadata payloads into the new meta column before dropping the redundant field.
        if (Schema::hasColumn('system_setting_categories', 'metadata')) {
            DB::table('system_setting_categories')
                ->whereNull('meta')
                ->whereNotNull('metadata')
                ->update(['meta' => DB::raw('metadata')]);

            Schema::table('system_setting_categories', function (Blueprint $table): void {
                $table->dropColumn('metadata');
            });
        }

        // Add a meta column to translations so locale-specific payloads can be persisted alongside name/description pairs.
        if (! Schema::hasColumn('system_setting_category_translations', 'meta')) {
            Schema::table('system_setting_category_translations', function (Blueprint $table): void {
                $table->json('meta')->nullable()->after('description')->comment('Per-locale metadata payload for category rendering');
            });
        }
    }

    public function down(): void
    {
        // Recreate the legacy metadata column so down migrations retain any stored payloads.
        if (! Schema::hasColumn('system_setting_categories', 'metadata')) {
            Schema::table('system_setting_categories', function (Blueprint $table): void {
                $table->json('metadata')->nullable()->after('template')->comment('Legacy metadata payload retained for rollbacks');
            });
        }

        // Move the structured data back to the metadata column before cleaning up the new meta column.
        if (Schema::hasColumn('system_setting_categories', 'meta')) {
            DB::table('system_setting_categories')
                ->whereNull('metadata')
                ->whereNotNull('meta')
                ->update(['metadata' => DB::raw('meta')]);

            Schema::table('system_setting_categories', function (Blueprint $table): void {
                $table->dropColumn('meta');
            });
        }

        // Drop the translation meta column to restore the previous schema shape.
        if (Schema::hasColumn('system_setting_category_translations', 'meta')) {
            Schema::table('system_setting_category_translations', function (Blueprint $table): void {
                $table->dropColumn('meta');
            });
        }
    }
};
