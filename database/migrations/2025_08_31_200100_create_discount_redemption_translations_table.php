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
        Schema::create('discount_redemption_translations', function (Blueprint $table) {
            $table->id();
            // Defer FK to discount_redemptions table to avoid ordering issues
            $table->unsignedBigInteger('discount_redemption_id');
            $table->string('locale', 5);
            $table->text('notes')->nullable();
            $table->string('status_description')->nullable();
            $table->json('metadata_description')->nullable();
            $table->timestamps();

            $table->unique(['discount_redemption_id', 'locale'], 'drt_redemption_locale_unique');
            $table->index(['locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_redemption_translations');
    }
};
