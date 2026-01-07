<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('commentable'); // polymorphic relation
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete(); // self-referencing for nested comments
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->integer('likes_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Critical: Composite index for polymorphic relationships - essential for performance
            $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_index');

            // Optimized composite indexes for common query patterns
            $table->index(['commentable_type', 'commentable_id', 'is_approved'], 'comments_commentable_approved_index');
            $table->index(['commentable_type', 'commentable_id', 'created_at'], 'comments_commentable_created_index');
            $table->index(['commentable_type', 'commentable_id', 'parent_id'], 'comments_commentable_parent_index');

            // Existing indexes
            $table->index(['parent_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
