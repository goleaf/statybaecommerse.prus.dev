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
            $table->foreignId('discount_redemption_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5)->index();
            $table->text('notes')->nullable();
            $table->string('status_description')->nullable();
            $table->json('metadata_description')->nullable();
            $table->timestamps();

            $table->unique(['discount_redemption_id', 'locale']);
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

