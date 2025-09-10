<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_visible')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->string('author_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sh_news_translations')) {
            Schema::create('sh_news_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('news_id');
                $table->string('locale', 10);
                $table->string('title');
                $table->string('slug');
                $table->text('summary')->nullable();
                $table->longText('content')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();

                $table->index('locale');
                $table->unique(['news_id', 'locale']);
                $table->unique(['locale', 'slug']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_news_translations');
        Schema::dropIfExists('news');
    }
};
