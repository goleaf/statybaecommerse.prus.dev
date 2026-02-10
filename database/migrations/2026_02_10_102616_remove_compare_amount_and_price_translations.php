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
        // Drop price_translations table
        Schema::dropIfExists('price_translations');

        // Remove compare_amount from prices table
        if (Schema::hasColumn('prices', 'compare_amount')) {
            Schema::table('prices', function (Blueprint $table) {
                $table->dropColumn('compare_amount');
            });
        }

        // Remove compare_amount from price_list_items table
        if (Schema::hasColumn('price_list_items', 'compare_amount')) {
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->dropColumn('compare_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add compare_amount back to prices
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('compare_amount', 12, 4)->nullable();
        });

        // Add compare_amount back to price_list_items
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->decimal('compare_amount', 12, 4)->nullable();
        });

        // Recreate price_translations table
        Schema::create('price_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained('prices')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['price_id', 'locale']);
            $table->index(['locale', 'name']);
        });
    }
};