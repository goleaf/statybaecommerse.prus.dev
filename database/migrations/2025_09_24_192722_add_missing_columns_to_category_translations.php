<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('category_translations')) {
            Schema::table('category_translations', function (Blueprint $table): void {
                if (! Schema::hasColumn('category_translations', 'short_description')) {
                    $table->text('short_description')->nullable()->after('description');
                }
                if (! Schema::hasColumn('category_translations', 'seo_keywords')) {
                    $table->string('seo_keywords')->nullable()->after('seo_description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('category_translations')) {
            Schema::table('category_translations', function (Blueprint $table): void {
                if (Schema::hasColumn('category_translations', 'short_description')) {
                    $table->dropColumn('short_description');
                }
                if (Schema::hasColumn('category_translations', 'seo_keywords')) {
                    $table->dropColumn('seo_keywords');
                }
            });
        }
    }
};
