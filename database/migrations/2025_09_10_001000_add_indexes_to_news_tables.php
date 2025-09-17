<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                $table->index(['is_visible', 'published_at'], 'news_visible_published_idx');
                $table->index('published_at', 'news_published_at_idx');
                $table->index('created_at', 'news_created_at_idx');
            });
        }

        if (Schema::hasTable('sh_news_translations')) {
            Schema::table('sh_news_translations', function (Blueprint $table): void {
                $table->index('news_id', 'sh_news_translations_news_id_idx');
                $table->index('created_at', 'sh_news_translations_created_at_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                $table->dropIndex('news_visible_published_idx');
                $table->dropIndex('news_published_at_idx');
                $table->dropIndex('news_created_at_idx');
            });
        }
        if (Schema::hasTable('sh_news_translations')) {
            Schema::table('sh_news_translations', function (Blueprint $table): void {
                $table->dropIndex('sh_news_translations_news_id_idx');
                $table->dropIndex('sh_news_translations_created_at_idx');
            });
        }
    }
};
