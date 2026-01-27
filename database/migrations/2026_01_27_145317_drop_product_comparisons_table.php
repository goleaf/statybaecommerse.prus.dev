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
        Schema::dropIfExists('product_comparisons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_comparisons', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['session_id', 'product_id']);
            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
        });
    }
};