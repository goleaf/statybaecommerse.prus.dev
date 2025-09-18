<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('collection_translations')) {
            return;
        }

        $hasSeoDescription = Schema::hasColumn('collection_translations', 'seo_description');
        $hasMetaTitle = Schema::hasColumn('collection_translations', 'meta_title');
        $hasMetaDescription = Schema::hasColumn('collection_translations', 'meta_description');
        $hasMetaKeywords = Schema::hasColumn('collection_translations', 'meta_keywords');

        Schema::table('collection_translations', function (Blueprint $table) use ($hasSeoDescription, $hasMetaTitle, $hasMetaDescription, $hasMetaKeywords) {
            if (! $hasMetaTitle) {
                $column = $table->string('meta_title')->nullable();

                if ($hasSeoDescription) {
                    $column->after('seo_description');
                }
            }

            if (! $hasMetaDescription) {
                $column = $table->text('meta_description')->nullable();

                if (! $hasMetaTitle && Schema::hasColumn('collection_translations', 'meta_title')) {
                    $column->after('meta_title');
                }
            }

            if (! $hasMetaKeywords) {
                $column = $table->json('meta_keywords')->nullable();

                if (! $hasMetaDescription && Schema::hasColumn('collection_translations', 'meta_description')) {
                    $column->after('meta_description');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('collection_translations')) {
            return;
        }

        Schema::table('collection_translations', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('collection_translations', 'meta_title') ? 'meta_title' : null,
                Schema::hasColumn('collection_translations', 'meta_description') ? 'meta_description' : null,
                Schema::hasColumn('collection_translations', 'meta_keywords') ? 'meta_keywords' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
