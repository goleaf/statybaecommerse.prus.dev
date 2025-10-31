<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard against installations where the legacy rename migration did not run yet.
        if (! Schema::hasTable('attribute_value_translations')) {
            return;
        }

        // Add the missing columns so factories, seeders, and admin forms share the same schema.
        if (! Schema::hasColumn('attribute_value_translations', 'description')) {
            Schema::table('attribute_value_translations', function (Blueprint $table): void {
                // Store localized marketing copy describing an attribute value, placed after the main value column.
                $table->text('description')->nullable()->after('value');
            });
        }

        if (! Schema::hasColumn('attribute_value_translations', 'meta_data')) {
            Schema::table('attribute_value_translations', function (Blueprint $table): void {
                // Capture optional structured details (e.g., color codes) for downstream integrations.
                $table->json('meta_data')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        // Guard against rollbacks on environments that never received the added columns.
        if (! Schema::hasTable('attribute_value_translations')) {
            return;
        }

        Schema::table('attribute_value_translations', function (Blueprint $table): void {
            // Drop the optional columns if present while leaving other legacy fields untouched.
            if (Schema::hasColumn('attribute_value_translations', 'meta_data')) {
                $table->dropColumn('meta_data');
            }

            if (Schema::hasColumn('attribute_value_translations', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
