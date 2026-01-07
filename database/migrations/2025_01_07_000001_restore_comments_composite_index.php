<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Restore the critical composite index for polymorphic relationships
            // This index is essential for efficient queries like $model->comments()
            $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_index');

            // Add optimized composite indexes for common query patterns
            $table->index(['commentable_type', 'commentable_id', 'is_approved'], 'comments_commentable_approved_index');
            $table->index(['commentable_type', 'commentable_id', 'created_at'], 'comments_commentable_created_index');
            $table->index(['commentable_type', 'commentable_id', 'parent_id'], 'comments_commentable_parent_index');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_commentable_index');
            $table->dropIndex('comments_commentable_approved_index');
            $table->dropIndex('comments_commentable_created_index');
            $table->dropIndex('comments_commentable_parent_index');
        });
    }
};
