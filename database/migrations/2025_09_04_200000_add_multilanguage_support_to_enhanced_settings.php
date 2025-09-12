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
                $table->string('locale', 10)->default('lt')->after('key');
                $table->index('locale');
            });
        }

        // Update the unique constraint to include locale
        Schema::table('enhanced_settings', function (Blueprint $table): void {
            $table->dropUnique(['key']);
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        // Restore original unique constraint
        Schema::table('enhanced_settings', function (Blueprint $table): void {
            $table->dropUnique(['key', 'locale']);
            $table->unique(['key']);
        });

        // Remove locale column
        if (Schema::hasColumn('enhanced_settings', 'locale')) {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });
        }

        // Drop translations table
        Schema::dropIfExists('enhanced_settings_translations');
    }
};
