<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_tags')) {
            return;
        }

        Schema::table('news_tags', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_tags', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('news_tags', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            if (! Schema::hasColumn('news_tags', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('news_tags', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_visible');
                $table->index('is_active');
            }
        });

        DB::table('news_tags')->orderBy('id')->lazyById()->each(function ($tag): void {
            $name = $tag->name ?: 'News Tag ' . $tag->id;
            $slug = $tag->slug ?: Str::slug($name . '-' . $tag->id);

            DB::table('news_tags')
                ->where('id', $tag->id)
                ->update([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $tag->description,
                ]);
        });

        try {
            Schema::table('news_tags', function (Blueprint $table): void {
                $table->unique('slug');
            });
        } catch (Throwable $exception) {
            // Ignore if the unique index already exists.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('news_tags')) {
            return;
        }

        Schema::table('news_tags', function (Blueprint $table): void {
            if (Schema::hasColumn('news_tags', 'slug')) {
                try {
                    $table->dropUnique(['slug']);
                } catch (Throwable $exception) {
                    // Ignore if the index was already removed.
                }
            }
        });

        Schema::table('news_tags', function (Blueprint $table): void {
            if (Schema::hasColumn('news_tags', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('news_tags', 'slug')) {
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('news_tags', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('news_tags', 'is_active')) {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            }
        });
    }
};
