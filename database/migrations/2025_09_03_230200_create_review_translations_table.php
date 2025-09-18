<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'locale']);
            $table->index(['locale', 'review_id']);
        });

        // Add missing columns to reviews table if they don't exist
        if (Schema::hasTable('reviews')) {
            $hasIsApprovedColumn = Schema::hasColumn('reviews', 'is_approved');
            $hasCommentColumn = Schema::hasColumn('reviews', 'comment');
            $hasContentColumn = Schema::hasColumn('reviews', 'content');

            Schema::table('reviews', function (Blueprint $table) use ($hasIsApprovedColumn, $hasCommentColumn, $hasContentColumn): void {
                if (! Schema::hasColumn('reviews', 'is_featured')) {
                    $column = $table->boolean('is_featured')->default(false);

                    if ($hasIsApprovedColumn) {
                        $column->after('is_approved');
                    }
                }

                if (! Schema::hasColumn('reviews', 'metadata')) {
                    $column = $table->json('metadata')->nullable();

                    if ($hasCommentColumn) {
                        $column->after('comment');
                    } elseif ($hasContentColumn) {
                        $column->after('content');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_translations');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $columns = array_filter([
                    Schema::hasColumn('reviews', 'is_featured') ? 'is_featured' : null,
                    Schema::hasColumn('reviews', 'metadata') ? 'metadata' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
