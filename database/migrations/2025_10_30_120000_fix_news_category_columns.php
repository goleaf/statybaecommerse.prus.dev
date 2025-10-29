<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news_categories')) {
            Schema::table('news_categories', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_categories', 'description')) {
                    // Reintroduce the missing description so factories and UI editors remain stable.
                    $table->text('description')->nullable()->after('slug');
                }
            });
        }

        if (Schema::hasTable('news_category_translations')) {
            Schema::table('news_category_translations', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_category_translations', 'description')) {
                    // Ensure translated descriptions exist for each locale to keep HasTranslations consistent.
                    $table->text('description')->nullable()->after('slug');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news_category_translations')) {
            Schema::table('news_category_translations', function (Blueprint $table): void {
                if (Schema::hasColumn('news_category_translations', 'description')) {
                    // Drop the description column only during rollbacks so production data stays intact otherwise.
                    $table->dropColumn('description');
                }
            });
        }

        if (Schema::hasTable('news_categories')) {
            Schema::table('news_categories', function (Blueprint $table): void {
                if (Schema::hasColumn('news_categories', 'description')) {
                    // Match the rollback path for environments that never had the column populated.
                    $table->dropColumn('description');
                }
            });
        }
    }
};
