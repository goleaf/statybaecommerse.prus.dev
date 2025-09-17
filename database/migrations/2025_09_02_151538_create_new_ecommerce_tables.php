<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create brands table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('website')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_enabled', 'name']);
                $table->index(['sort_order']);
            });
        }

        // Create categories table
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_visible')->default(true);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['is_visible', 'sort_order']);
                $table->index(['parent_id', 'sort_order']);
            });
        }

        // Create products table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->string('sku')->unique();
                $table->text('summary')->nullable();
                $table->decimal('price', 15, 2)->nullable();
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->decimal('compare_price', 15, 2)->nullable();
                $table->decimal('cost_price', 15, 2)->nullable();
                $table->boolean('manage_stock')->default(false);
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(0);
                $table->decimal('weight', 8, 2)->nullable();
                $table->decimal('length', 8, 2)->nullable();
                $table->decimal('width', 8, 2)->nullable();
                $table->decimal('height', 8, 2)->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->enum('type', ['simple', 'variable'])->default('simple');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['is_visible', 'published_at']);
                $table->index(['status', 'is_visible']);
                $table->index(['brand_id', 'is_visible']);
                $table->index(['is_featured', 'is_visible']);
            });
        }

        // Create product_categories pivot table
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['product_id', 'category_id']);
            });
        }

        // Create collections table
        if (!Schema::hasTable('collections')) {
            Schema::create('collections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_visible', 'sort_order']);
                $table->index(['is_enabled']);
            });
        }

        // Create product_collections pivot table
        if (!Schema::hasTable('product_collections')) {
            Schema::create('product_collections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('collection_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('collection_id')->references('id')->on('collections')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['product_id', 'collection_id']);
            });
        }

        // Create orders table
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('shipping_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('currency', 3)->default('EUR');
                $table->json('billing_address')->nullable();
                $table->json('shipping_address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['status', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // Create order_items table
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->string('product_name');
                $table->string('product_sku');
                $table->integer('quantity');
                $table->decimal('price', 15, 2);
                $table->decimal('total', 15, 2);
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        // Create reviews table
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('reviewer_name');
                $table->string('reviewer_email');
                $table->integer('rating');
                $table->string('title')->nullable();
                $table->text('content');
                $table->boolean('is_approved')->default(false);
                $table->string('locale', 8)->default('lt');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['product_id', 'is_approved']);
                $table->index(['is_approved', 'created_at']);
                $table->index(['locale']);
            });
        }

        // Create coupons table
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['percentage', 'fixed']);
                $table->decimal('value', 15, 2);
                $table->decimal('minimum_amount', 15, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['code', 'is_active']);
                $table->index(['is_active', 'starts_at', 'expires_at']);
            });
        }

        // Create cart_items table for session-based cart
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->string('session_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->json('product_snapshot')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['session_id']);
                $table->index(['user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_collections');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('product_categories');
        // Schema::dropIfExists('products'); // DISABLED: Prevent products table deletion
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
