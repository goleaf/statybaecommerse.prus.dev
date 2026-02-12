<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createDiscountProductsTable();
        $this->createDiscountBrandsTable();
        $this->createDiscountCustomersTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_customers');
        Schema::dropIfExists('discount_brands');
        Schema::dropIfExists('discount_products');
    }

    private function createDiscountProductsTable(): void
    {
        if (Schema::hasTable('discount_products')) {
            return;
        }

        Schema::create('discount_products', function (Blueprint $table): void {
            $table->id();

            if (Schema::hasTable('discounts')) {
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('discount_id')->index();
            }

            if (Schema::hasTable('products')) {
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('product_id')->index();
            }

            $table->timestamps();

            $table->unique(['discount_id', 'product_id'], 'discount_products_discount_product_unique');
            $table->index(['product_id', 'discount_id'], 'discount_products_product_discount_index');
        });
    }

    private function createDiscountBrandsTable(): void
    {
        if (Schema::hasTable('discount_brands')) {
            return;
        }

        Schema::create('discount_brands', function (Blueprint $table): void {
            $table->id();

            if (Schema::hasTable('discounts')) {
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('discount_id')->index();
            }

            if (Schema::hasTable('brands')) {
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('brand_id')->index();
            }

            $table->timestamps();

            $table->unique(['discount_id', 'brand_id'], 'discount_brands_discount_brand_unique');
            $table->index(['brand_id', 'discount_id'], 'discount_brands_brand_discount_index');
        });
    }

    private function createDiscountCustomersTable(): void
    {
        if (Schema::hasTable('discount_customers')) {
            return;
        }

        Schema::create('discount_customers', function (Blueprint $table): void {
            $table->id();

            if (Schema::hasTable('discounts')) {
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('discount_id')->index();
            }

            if (Schema::hasTable('users')) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('user_id')->index();
            }

            $table->timestamps();

            $table->unique(['discount_id', 'user_id'], 'discount_customers_discount_user_unique');
            $table->index(['user_id', 'discount_id'], 'discount_customers_user_discount_index');
        });
    }
};
