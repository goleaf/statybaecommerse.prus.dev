<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update price_lists table to add new fields
        Schema::table('price_lists', function (Blueprint $table) {
            $table->text('description')->nullable()->after('code');
            $table->json('metadata')->nullable()->after('description');
            $table->boolean('is_default')->default(false)->after('is_enabled');
            $table->boolean('auto_apply')->default(false)->after('is_default');
            $table->decimal('min_order_amount', 12, 2)->nullable()->after('auto_apply');
            $table->decimal('max_order_amount', 12, 2)->nullable()->after('min_order_amount');
        });

        // Create price_list_translations table
        Schema::create('price_list_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->timestamps();

            $table->unique(['price_list_id', 'locale']);
            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
            $table->index(['locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_translations');
        
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'metadata',
                'is_default',
                'auto_apply',
                'min_order_amount',
                'max_order_amount',
            ]);
        });
    }
};
