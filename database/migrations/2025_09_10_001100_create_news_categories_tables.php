<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_categories')) {
            Schema::create('news_categories', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_visible')->default(true);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('color')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();

                $table->index('is_visible');
                $table->index('parent_id');
                $table->index('sort_order');
            });
        }

        if (! Schema::hasTable('sh_news_category_translations')) {
            Schema::create('sh_news_category_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('news_category_id');
                $table->string('locale', 10);
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('locale');
                $table->unique(['news_category_id', 'locale']);
                $table->unique(['locale', 'slug']);
            });
        }

        if (! Schema::hasTable('news_category_pivot')) {
            Schema::create('news_category_pivot', function (Blueprint $table): void {
                $table->unsignedBigInteger('news_id');
                $table->unsignedBigInteger('news_category_id');
                $table->primary(['news_id', 'news_category_id']);
                $table->index('news_id');
                $table->index('news_category_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news_category_pivot');
        Schema::dropIfExists('sh_news_category_translations');
        Schema::dropIfExists('news_categories');
    }
};
