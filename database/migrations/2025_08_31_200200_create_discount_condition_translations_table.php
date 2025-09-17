<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discount_condition_translations', function (Blueprint $table) {
            $table->id();
            // Defer FK to discount_conditions table to avoid ordering issues
            $table->unsignedBigInteger('discount_condition_id');
            $table->string('locale', 5);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['discount_condition_id', 'locale'], 'dct_condition_locale_unique');
            $table->index(['locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_condition_translations');
    }
};
