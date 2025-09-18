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
        Schema::table('campaign_discount', function (Blueprint $table) {
            // Add foreign key constraints
            $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnDelete();
            $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            
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
        Schema::table('campaign_discount', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['discount_id']);
            
            // Drop unique constraint
            $table->dropUnique(['campaign_id', 'discount_id']);
            
            // Drop indexes
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['discount_id']);
        });
    }
};
