<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->onDelete('cascade');
            $table->string('locale', 5)->index();
            $table->string('value')->nullable();
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->unique(['attribute_value_id', 'locale'], 'attribute_value_translations_unique');
            $table->index(['locale', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_translations');
    }
};
