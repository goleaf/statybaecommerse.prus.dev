<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discount_categories')) {
            return;
        }

        if (Schema::hasTable('sh_discount_categories')) {
            Schema::rename('sh_discount_categories', 'discount_categories');

            return;
        }

        Schema::create('discount_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_id', 'category_id'], 'discount_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_categories');
    }
};

