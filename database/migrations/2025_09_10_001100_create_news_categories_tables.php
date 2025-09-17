<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_categories', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_visible')->default(true);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('news_categories')->nullOnDelete()->cascadeOnUpdate();
            $table->index('is_visible', 'news_cat_visible_idx');
            $table->index('parent_id', 'news_cat_parent_idx');
            $table->index('sort_order', 'news_cat_sort_idx');
        });

        Schema::create('sh_news_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_category_id');
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('news_category_id')->references('id')->on('news_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index('locale', 'news_cat_tr_locale_idx');
            $table->unique(['news_category_id', 'locale'], 'news_cat_tr_category_locale_unique');
            $table->unique(['locale', 'slug'], 'news_cat_tr_locale_slug_unique');
        });

        Schema::create('news_category_pivot', function (Blueprint $table): void {
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('news_category_id');
            $table->primary(['news_id', 'news_category_id']);
            $table->index('news_id', 'news_cat_pivot_news_idx');
            $table->index('news_category_id', 'news_cat_pivot_category_idx');

            $table->foreign('news_id')->references('id')->on('news')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('news_category_id')->references('id')->on('news_categories')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_category_pivot');
        Schema::dropIfExists('sh_news_category_translations');
        Schema::dropIfExists('news_categories');
    }
};
