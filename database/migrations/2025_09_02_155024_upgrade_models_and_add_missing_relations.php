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
        // Upgrade orders table (forward-only)
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (!Schema::hasColumn('orders', 'channel_id')) {
                    $table->unsignedBigInteger('channel_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('orders', 'zone_id')) {
                    $table->unsignedBigInteger('zone_id')->nullable()->after('channel_id');
                }
                if (!Schema::hasColumn('orders', 'partner_id')) {
                    $table->unsignedBigInteger('partner_id')->nullable()->after('zone_id');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending')->after('status');
                }
                if (!Schema::hasColumn('orders', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('orders', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('payment_method');
                }
            });
        }

        // Upgrade order_items
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table): void {
                if (!Schema::hasColumn('order_items', 'variant_id')) {
                    $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('order_items', 'variant_name')) {
                    $table->string('variant_name')->nullable()->after('product_sku');
                }
            });
        }

        // Upgrade cart_items
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table): void {
                if (!Schema::hasColumn('cart_items', 'variant_id')) {
                    $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                }
            });
        }

        // Create locations table
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->string('code')->nullable()->unique();
                $table->string('address_line_1')->nullable();
                $table->string('address_line_2')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country_code', 3)->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->enum('type', ['warehouse', 'store', 'pickup_point'])->default('warehouse');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create attributes table
        if (!Schema::hasTable('sh_attributes')) {
            Schema::create('sh_attributes', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->enum('type', ['text', 'number', 'select', 'multiselect', 'boolean', 'date', 'color'])->default('text');
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create attribute values table
        if (!Schema::hasTable('sh_attribute_values')) {
            Schema::create('sh_attribute_values', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value');
                $table->string('key')->nullable();
                $table->integer('position')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('attribute_id')->references('id')->on('sh_attributes')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        // Product attributes pivot
        if (!Schema::hasTable('sh_product_attributes')) {
            Schema::create('sh_product_attributes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('attribute_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('attribute_id')->references('id')->on('sh_attributes')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['product_id', 'attribute_id']);
            });
        }

        // Product variant attributes pivot
        if (!Schema::hasTable('sh_product_variant_attributes')) {
            Schema::create('sh_product_variant_attributes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('attribute_value_id')->references('id')->on('sh_attribute_values')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['variant_id', 'attribute_value_id'], 'variant_attribute_value_unique');
            });
        }

        // Group price list pivot
        if (!Schema::hasTable('sh_group_price_list')) {
            Schema::create('sh_group_price_list', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('customer_group_id');
                $table->unsignedBigInteger('price_list_id');
                $table->timestamps();

                $table->foreign('customer_group_id')->references('id')->on('customer_groups')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('price_list_id')->references('id')->on('sh_price_lists')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['customer_group_id', 'price_list_id']);
            });
        }

        // Partner relations pivots
        if (!Schema::hasTable('sh_partner_price_list')) {
            Schema::create('sh_partner_price_list', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('price_list_id');
                $table->timestamps();

                $table->foreign('partner_id')->references('id')->on('sh_partners')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('price_list_id')->references('id')->on('sh_price_lists')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['partner_id', 'price_list_id']);
            });
        }

        if (!Schema::hasTable('sh_partner_users')) {
            Schema::create('sh_partner_users', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('partner_id')->references('id')->on('sh_partners')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['partner_id', 'user_id']);
            });
        }

        // Add performance indexes for orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                try {
                    $table->index(['status', 'created_at'], 'orders_status_created_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop pivot tables
        Schema::dropIfExists('sh_product_variant_attributes');
        Schema::dropIfExists('sh_product_attributes');
        Schema::dropIfExists('sh_discount_products');
        Schema::dropIfExists('sh_discount_customer_groups');
        Schema::dropIfExists('sh_group_price_list');
        Schema::dropIfExists('sh_partner_price_list');
        Schema::dropIfExists('sh_partner_users');

        // Drop new tables
        Schema::dropIfExists('sh_price_list_items');
        Schema::dropIfExists('sh_price_lists');
        Schema::dropIfExists('sh_variant_inventories');
        Schema::dropIfExists('sh_order_shippings');
        Schema::dropIfExists('sh_partners');
        Schema::dropIfExists('sh_partner_tiers');
        Schema::dropIfExists('sh_locations');
        Schema::dropIfExists('sh_attributes');
        Schema::dropIfExists('sh_attribute_values');
    }
};
