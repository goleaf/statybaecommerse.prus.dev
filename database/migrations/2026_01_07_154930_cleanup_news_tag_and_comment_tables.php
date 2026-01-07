<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration removes NewsTag and NewsComment related tables while preserving
     * referential integrity for remaining data. Tables are dropped in the correct
     * order to handle foreign key constraints properly.
     */
    public function up(): void
    {
        // Drop pivot table first (has foreign keys to news_tags and news tables)
        if (Schema::hasTable('news_tag_pivot')) {
            Schema::dropIfExists('news_tag_pivot');
        }

        // Drop news_comments table (has foreign key to news table and self-referencing parent_id)
        if (Schema::hasTable('news_comments')) {
            Schema::dropIfExists('news_comments');
        }

        // Drop news_tag_translations table (has foreign key to news_tags)
        if (Schema::hasTable('news_tag_translations')) {
            Schema::dropIfExists('news_tag_translations');
        }

        // Also check for the old name (sh_news_tag_translations) in case it still exists
        if (Schema::hasTable('sh_news_tag_translations')) {
            Schema::dropIfExists('sh_news_tag_translations');
        }

        // Drop news_tags table last
        if (Schema::hasTable('news_tags')) {
            Schema::dropIfExists('news_tags');
        }
    }

    /**
     * Reverse the migrations.
     *
     * Note: This rollback recreates the basic table structure but does not restore data.
     * This is intentional as this migration is designed for permanent cleanup.
     */
    public function down(): void
    {
        // Recreate news_tags table
        if (! Schema::hasTable('news_tags')) {
            Schema::create('news_tags', function ($table) {
                $table->id();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_active')->default(true);
                $table->string('color', 7)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('is_visible');
                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        // Recreate news_tag_translations table
        if (! Schema::hasTable('news_tag_translations')) {
            Schema::create('news_tag_translations', function ($table) {
                $table->id();
                $table->unsignedBigInteger('news_tag_id');
                $table->string('locale', 10);
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('locale');
                $table->unique(['news_tag_id', 'locale']);
                $table->unique(['locale', 'slug']);
            });
        }

        // Recreate news_tag_pivot table
        if (! Schema::hasTable('news_tag_pivot')) {
            Schema::create('news_tag_pivot', function ($table) {
                $table->unsignedBigInteger('news_id');
                $table->unsignedBigInteger('news_tag_id');
                $table->primary(['news_id', 'news_tag_id']);
                $table->index('news_id');
                $table->index('news_tag_id');
            });
        }

        // Recreate news_comments table
        if (! Schema::hasTable('news_comments')) {
            Schema::create('news_comments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('news_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('author_name');
                $table->string('author_email');
                $table->text('content');
                $table->boolean('is_approved')->default(false);
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('news_id');
                $table->index('parent_id');
                $table->index('is_approved');
                $table->index('is_visible');
                $table->index('is_active');
            });
        }
    }
};
