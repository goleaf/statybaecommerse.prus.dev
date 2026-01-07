<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('mime_type');
            $table->bigInteger('size'); // in bytes
            $table->string('hash')->nullable(); // for duplicate detection
            $table->morphs('fileable'); // polymorphic relation
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['uploaded_by']);
            $table->index(['hash']);
            $table->index(['mime_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
