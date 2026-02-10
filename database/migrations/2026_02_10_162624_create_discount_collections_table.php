<?php

declare(strict_types=1);

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
        if (! Schema::hasTable('discount_collections')) {
            Schema::create('discount_collections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
                $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['discount_id', 'collection_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_collections');
    }
};