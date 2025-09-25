<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create coupon_products pivot table
        if (! Schema::hasTable('coupon_products')) {
            Schema::create('coupon_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['coupon_id', 'product_id']);
                $table->index(['coupon_id']);
                $table->index(['product_id']);
            });
        }

        // Create coupon_categories pivot table
        if (! Schema::hasTable('coupon_categories')) {
            Schema::create('coupon_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['coupon_id', 'category_id']);
                $table->index(['coupon_id']);
                $table->index(['category_id']);
            });
        }

        // Create coupon_usages table for tracking usage
        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->timestamp('used_at');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['coupon_id']);
                $table->index(['user_id']);
                $table->index(['order_id']);
                $table->index(['used_at']);
            });
        }

        // Add coupon_id to orders table if it doesn't exist
        if (! Schema::hasColumn('orders', 'coupon_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
                $table->index(['coupon_id']);
            });
        }

        // Update coupons table to add missing fields
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'maximum_discount')) {
                $table->decimal('maximum_discount', 10, 2)->nullable()->after('minimum_amount');
            }
            if (! Schema::hasColumn('coupons', 'usage_limit_per_user')) {
                $table->integer('usage_limit_per_user')->nullable()->after('usage_limit');
            }
            if (! Schema::hasColumn('coupons', 'applicable_products')) {
                $table->json('applicable_products')->nullable()->after('expires_at');
            }
            if (! Schema::hasColumn('coupons', 'applicable_categories')) {
                $table->json('applicable_categories')->nullable()->after('applicable_products');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_products');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'maximum_discount',
                'usage_limit_per_user',
                'applicable_products',
                'applicable_categories',
            ]);
        });
    }
};
