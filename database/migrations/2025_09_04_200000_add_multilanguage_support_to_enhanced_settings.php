<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create enhanced_settings_translations table
        if (! Schema::hasTable('enhanced_settings_translations')) {
            Schema::create('enhanced_settings_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('enhanced_setting_id');
                $table->string('locale', 10);
                $table->text('description')->nullable();
                $table->string('display_name')->nullable(); // Human-readable name for the setting
                $table->text('help_text')->nullable(); // Additional help text
                $table->timestamps();

                $table->index('locale');
                $table->unique(['enhanced_setting_id', 'locale']);
                $table->foreign('enhanced_setting_id')->references('id')->on('enhanced_settings')->onDelete('cascade');
            });
        }

        // Add locale column to enhanced_settings table if it doesn't exist
        if (! Schema::hasColumn('enhanced_settings', 'locale')) {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                // Add the locale column with an explicit index so lookups stay fast on multilingual installs.
                $table->string('locale', 10)->default('lt')->after('key');
                $table->index('locale', 'enhanced_settings_locale_index');
            });
        } else {
            // If earlier migrations already introduced the column, ensure the supporting index exists.
            try {
                Schema::table('enhanced_settings', function (Blueprint $table): void {
                    $table->index('locale', 'enhanced_settings_locale_index');
                });
            } catch (\Throwable) {
                // Ignore duplicate index errors because some environments already applied the optimisation.
            }
        }

        // Update the unique constraint to include locale
        try {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->dropUnique('enhanced_settings_key_unique');
            });
        } catch (\Throwable) {
            // Some fresh databases never created the legacy unique index, so dropping it is optional.
        }

        try {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->unique(['key', 'locale'], 'enhanced_settings_key_locale_unique');
            });
        } catch (\Throwable) {
            // Ignore duplicate creation attempts when the composite index already exists.
        }
    }

    public function down(): void
    {
        // Restore original unique constraint
        try {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->dropUnique('enhanced_settings_key_locale_unique');
            });
        } catch (\Throwable) {
            // Ignore missing composite indexes because some environments may have already reverted them.
        }

        try {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->unique('key', 'enhanced_settings_key_unique');
            });
        } catch (\Throwable) {
            // Ignore duplicate creation errors when the legacy index is already present.
        }

        // Remove locale column
        if (Schema::hasColumn('enhanced_settings', 'locale')) {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                try {
                    $table->dropIndex('enhanced_settings_locale_index');
                } catch (\Throwable) {
                    // Ignore missing index failures so the column drop can continue safely.
                }
                $table->dropColumn('locale');
            });
        }

        // Drop translations table
        Schema::dropIfExists('enhanced_settings_translations');
    }
};
