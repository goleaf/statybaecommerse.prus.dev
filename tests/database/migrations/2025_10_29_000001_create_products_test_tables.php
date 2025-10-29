<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard clauses keep the test harness compatible with full migrations by
        // only provisioning fallback tables when the main schema is unavailable.
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                // Provide the catalogue columns exercised by factories and unit tests
                // so SQLite-based runs can persist Product records reliably.
                $table->id();
                $table->string('type')->default('simple');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('sku')->nullable();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(0);
                $table->decimal('weight', 8, 2)->nullable();
                $table->decimal('length', 8, 2)->nullable();
                $table->decimal('width', 8, 2)->nullable();
                $table->decimal('height', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->boolean('manage_stock')->default(false);
                $table->string('status')->default('draft');
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_visible', 'status']);
                $table->index(['brand_id']);
            });
        }

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table): void {
                // Mirror the relationship and ordering fields used in ProductImage tests
                // to ensure cascade deletes and scopes run identically under SQLite.
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('path');
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['product_id', 'sort_order']);
                $table->index(['is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
