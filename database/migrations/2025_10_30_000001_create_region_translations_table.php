<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('region_translations')) {
            return;
        }

        Schema::create('region_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();

            $table->unique(['region_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_translations');
    }
};







