<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename sh_ prefixed news tables to remove the prefix
        if (Schema::hasTable('sh_news_category_translations') && ! Schema::hasTable('news_category_translations')) {
            Schema::rename('sh_news_category_translations', 'news_category_translations');
        }

        if (Schema::hasTable('sh_news_tag_translations') && ! Schema::hasTable('news_tag_translations')) {
            Schema::rename('sh_news_tag_translations', 'news_tag_translations');
        }

        if (Schema::hasTable('sh_news_translations') && ! Schema::hasTable('news_translations')) {
            Schema::rename('sh_news_translations', 'news_translations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the renames
        if (Schema::hasTable('news_category_translations') && ! Schema::hasTable('sh_news_category_translations')) {
            Schema::rename('news_category_translations', 'sh_news_category_translations');
        }

        if (Schema::hasTable('news_tag_translations') && ! Schema::hasTable('sh_news_tag_translations')) {
            Schema::rename('news_tag_translations', 'sh_news_tag_translations');
        }

        if (Schema::hasTable('news_translations') && ! Schema::hasTable('sh_news_translations')) {
            Schema::rename('news_translations', 'sh_news_translations');
        }
    }
};
