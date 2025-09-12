<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_item_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_item_id');
            $table->string('locale', 5);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('price_list_item_id')->references('id')->on('price_list_items')->onDelete('cascade');
            $table->unique(['price_list_item_id', 'locale'], 'price_list_item_translations_unique');
            $table->index(['locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_item_translations');
    }
};
