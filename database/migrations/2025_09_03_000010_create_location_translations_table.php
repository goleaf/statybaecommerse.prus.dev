<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations') || Schema::hasTable('location_translations')) {
            return;
        }

        Schema::create('location_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('locale', 2);
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_translations');
    }
};
