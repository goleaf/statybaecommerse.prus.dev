<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news_categories')) {
            Schema::table('news_categories', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_visible');
                    $table->index('is_active');
                }
            });
        }

        if (Schema::hasTable('news_tags')) {
            Schema::table('news_tags', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_tags', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_visible');
                    $table->index('is_active');
                }
            });
        }

        if (Schema::hasTable('news_comments')) {
            Schema::table('news_comments', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_comments', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_visible');
                    $table->index('is_active');
                }
            });
        }

        if (Schema::hasTable('news_images')) {
            Schema::table('news_images', function (Blueprint $table): void {
                if (! Schema::hasColumn('news_images', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('sort_order');
                    $table->index('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news_categories')) {
            Schema::table('news_categories', function (Blueprint $table): void {
                if (Schema::hasColumn('news_categories', 'is_active')) {
                    $table->dropIndex(['is_active']);
                    $table->dropColumn('is_active');
                }
            });
        }

        if (Schema::hasTable('news_tags')) {
            Schema::table('news_tags', function (Blueprint $table): void {
                if (Schema::hasColumn('news_tags', 'is_active')) {
                    $table->dropIndex(['is_active']);
                    $table->dropColumn('is_active');
                }
            });
        }

        if (Schema::hasTable('news_comments')) {
            Schema::table('news_comments', function (Blueprint $table): void {
                if (Schema::hasColumn('news_comments', 'is_active')) {
                    $table->dropIndex(['is_active']);
                    $table->dropColumn('is_active');
                }
            });
        }

        if (Schema::hasTable('news_images')) {
            Schema::table('news_images', function (Blueprint $table): void {
                if (Schema::hasColumn('news_images', 'is_active')) {
                    $table->dropIndex(['is_active']);
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};


