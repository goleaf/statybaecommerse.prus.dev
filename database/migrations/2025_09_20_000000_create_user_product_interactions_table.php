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
        Schema::create('user_product_interactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->string('event'); // Previously interaction_type, but subsequent migration expects event to exist or renames it. Let's use 'event' as newer standard or 'interaction_type' if that's what was expected.
            // The update migration says: if interaction_type exists, rename to event. So likely it started as interaction_type.
            
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('count')->default(1);
            $table->timestamp('first_interaction')->useCurrent();
            $table->timestamp('last_interaction')->useCurrent();
            $table->unsignedBigInteger('product_variant_id')->nullable(); 
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            // Named indexes to support future drops
            $table->unique(['user_id', 'product_id', 'event'], 'user_product_interaction_unique');
            $table->index(['user_id', 'event', 'last_interaction'], 'user_interactions_last_idx');
            $table->index(['product_id', 'event', 'count'], 'user_interactions_product_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_interactions');
    }
};
