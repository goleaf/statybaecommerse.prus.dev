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
        Schema::create('campaign_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate relationships
            $table->unique(['campaign_id', 'discount_id']);
            
            // Add indexes for better performance
            $table->index(['campaign_id']);
            $table->index(['discount_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_discount');
    }
};
