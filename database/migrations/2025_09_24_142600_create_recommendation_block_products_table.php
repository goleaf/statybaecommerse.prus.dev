<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recommendation_block_products')) {
            Schema::create('recommendation_block_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recommendation_block_id')
                    ->constrained('recommendation_blocks')
                    ->onDelete('cascade');
                $table->foreignId('product_id')
                    ->constrained()
                    ->onDelete('cascade');
                $table->timestamps();

                $table->unique(['recommendation_block_id', 'product_id'], 'rbp_unique');
                $table->index(['product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_block_products');
    }
};


