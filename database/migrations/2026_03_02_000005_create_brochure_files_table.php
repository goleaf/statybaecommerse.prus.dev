<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brochure_files')) {
            return;
        }

        Schema::create('brochure_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brochure_id')->constrained('brochures')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['brochure_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brochure_files');
    }
};
