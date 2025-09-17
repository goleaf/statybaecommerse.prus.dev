<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // News Tags table
        Schema::create('news_tags', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_visible')->default(true);
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_visible');
            $table->index('sort_order');
        });

        // News Tag Translations table
        Schema::create('sh_news_tag_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_tag_id');
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('news_tag_id')->references('id')->on('news_tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index('locale');
            $table->unique(['news_tag_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        // News Tag Pivot table
        Schema::create('news_tag_pivot', function (Blueprint $table): void {
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('news_tag_id');
            $table->primary(['news_id', 'news_tag_id']);
            $table->index('news_id');
            $table->index('news_tag_id');

            $table->foreign('news_id')->references('id')->on('news')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('news_tag_id')->references('id')->on('news_tags')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // News Comments table
        Schema::create('news_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('author_name');
            $table->string('author_email');
            $table->text('content');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->foreign('news_id')->references('id')->on('news')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('news_comments')->nullOnDelete()->cascadeOnUpdate();
            $table->index('news_id');
            $table->index('parent_id');
            $table->index('is_approved');
            $table->index('is_visible');
        });

        // News Images table
        Schema::create('news_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_id');
            $table->string('file_path');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->json('dimensions')->nullable();
            $table->timestamps();

            $table->foreign('news_id')->references('id')->on('news')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index('news_id');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_images');
        Schema::dropIfExists('news_comments');
        Schema::dropIfExists('news_tag_pivot');
        Schema::dropIfExists('sh_news_tag_translations');
        Schema::dropIfExists('news_tags');
    }
};

