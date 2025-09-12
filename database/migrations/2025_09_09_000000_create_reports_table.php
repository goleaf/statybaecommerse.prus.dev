<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->id();
            $table->json('name'); // Translatable
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('category')->index();
            $table->string('date_range')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('filters')->nullable();
            $table->json('description')->nullable(); // Translatable
            $table->longText('content')->nullable(); // Translatable
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'category']);
            $table->index(['is_active', 'is_public']);
            $table->index(['created_at', 'updated_at']);
            $table->index('view_count');
            $table->index('download_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
