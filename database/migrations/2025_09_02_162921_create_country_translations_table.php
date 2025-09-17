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
        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            // Defer FK to avoid ordering issues
            $table->unsignedBigInteger('country_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->string('name_official')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'locale'], 'ct_country_locale_unique');
            $table->index(['locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_translations');
    }
};
