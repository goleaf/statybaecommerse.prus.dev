<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create project_user pivot table
        if (! Schema::hasTable('project_user')) {
            Schema::create('project_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role')->default('member');
                $table->json('permissions')->nullable();
                $table->datetime('joined_at');
                $table->datetime('left_at')->nullable();
                $table->timestamps();

                $table->unique(['project_id', 'user_id']);
            });
        }

        // Create task_user pivot table
        if (! Schema::hasTable('task_user')) {
            Schema::create('task_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('responsibility')->default('assignee');
                $table->datetime('assigned_at');
                $table->datetime('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['task_id', 'user_id', 'responsibility']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('project_user');
    }
};
