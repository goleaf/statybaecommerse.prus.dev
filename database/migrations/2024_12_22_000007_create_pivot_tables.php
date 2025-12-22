<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User-Organization pivot with roles
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // owner, admin, member, viewer
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->datetime('joined_at');
            $table->datetime('left_at')->nullable();
            $table->timestamps();
            
            $table->unique(['organization_id', 'user_id']);
            $table->index(['user_id', 'role']);
            $table->index(['organization_id', 'is_active']);
        });

        // Task-User assignments with responsibilities
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('responsibility')->default('assignee'); // assignee, reviewer, watcher
            $table->datetime('assigned_at');
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['task_id', 'user_id', 'responsibility']);
            $table->index(['user_id', 'responsibility']);
        });

        // Polymorphic taggables
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->foreignId('tagged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('tagged_at');
            $table->timestamps();
            
            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
            $table->index(['taggable_type', 'taggable_id']);
        });

        // Project members (many-to-many with additional data)
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // lead, member, contributor
            $table->json('permissions')->nullable();
            $table->datetime('joined_at');
            $table->datetime('left_at')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('organization_user');
    }
};