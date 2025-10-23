<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->string('action', 50);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('diff')->nullable();
            $table->timestamps();

            // Common lookups rely on model identification and action filtering.
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
