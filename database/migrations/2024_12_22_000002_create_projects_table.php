<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, completed, archived
            $table->string('type')->default('organizational'); // personal, organizational
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // owner for personal projects
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete(); // for organizational projects
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['user_id', 'type']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
