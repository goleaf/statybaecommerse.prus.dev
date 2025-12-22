<?php

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
            
            $table->index(['commentable_type', 'commentable_id']);
            $table->index(['parent_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};