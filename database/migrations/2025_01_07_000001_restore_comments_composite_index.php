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
            if (! Schema::hasIndex('comments', 'comments_commentable_index')) {
                $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_index');
            }

            if (! Schema::hasIndex('comments', 'comments_commentable_approved_index')) {
                $table->index(['commentable_type', 'commentable_id', 'is_approved'], 'comments_commentable_approved_index');
            }

            if (! Schema::hasIndex('comments', 'comments_commentable_created_index')) {
                $table->index(['commentable_type', 'commentable_id', 'created_at'], 'comments_commentable_created_index');
            }

            if (! Schema::hasIndex('comments', 'comments_commentable_parent_index')) {
                $table->index(['commentable_type', 'commentable_id', 'parent_id'], 'comments_commentable_parent_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasIndex('comments', 'comments_commentable_index')) {
                $table->dropIndex('comments_commentable_index');
            }

            if (Schema::hasIndex('comments', 'comments_commentable_approved_index')) {
                $table->dropIndex('comments_commentable_approved_index');
            }

            if (Schema::hasIndex('comments', 'comments_commentable_created_index')) {
                $table->dropIndex('comments_commentable_created_index');
            }

            if (Schema::hasIndex('comments', 'comments_commentable_parent_index')) {
                $table->dropIndex('comments_commentable_parent_index');
            }
        });
    }
};
