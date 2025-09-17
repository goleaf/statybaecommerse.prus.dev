<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_customer_groups')) {
            Schema::create('sh_customer_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sh_customer_group_user')) {
            Schema::create('sh_customer_group_user', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('user_id');
                $table->primary(['group_id', 'user_id']);
                $table->foreign('group_id')->references('id')->on('sh_customer_groups')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('sh_partners')) {
            Schema::create('sh_partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->enum('tier', ['gold', 'silver', 'bronze', 'custom'])->default('custom');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->decimal('discount_rate', 6, 4)->default(0);
                $table->decimal('commission_rate', 6, 4)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            });
        }

        if (!Schema::hasTable('sh_partner_users')) {
            Schema::create('sh_partner_users', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('user_id');
                $table->primary(['partner_id', 'user_id']);
                $table->foreign('partner_id')->references('id')->on('sh_partners')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('sh_partner_tiers')) {
            Schema::create('sh_partner_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->decimal('discount_rate', 6, 4)->default(0);
                $table->decimal('commission_rate', 6, 4)->default(0);
                $table->decimal('minimum_order_value', 12, 2)->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->json('benefits')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('sh_price_lists')) {
            Schema::create('sh_price_lists', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('currency_id');
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedInteger('priority')->default(100);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
            Schema::table('sh_price_lists', function (Blueprint $table) {
                if (Schema::hasTable('sh_currencies')) {
                    $table->foreign('currency_id')->references('id')->on('sh_currencies')->cascadeOnUpdate();
                }
                if (Schema::hasTable('sh_zones')) {
                    $table->foreign('zone_id')->references('id')->on('sh_zones')->cascadeOnUpdate()->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('sh_price_list_items')) {
            Schema::create('sh_price_list_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('price_list_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->decimal('net_amount', 15, 2);
                $table->timestamps();

                $table->index(['price_list_id']);
                $table->index(['product_id']);
                $table->index(['variant_id']);

                $table->foreign('price_list_id')->references('id')->on('sh_price_lists')->cascadeOnUpdate()->cascadeOnDelete();
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->nullOnDelete();
                }
                if (Schema::hasTable('product_variants')) {
                    $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('sh_group_price_list')) {
            Schema::create('sh_group_price_list', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('price_list_id');
                $table->primary(['group_id', 'price_list_id']);
                $table->foreign('group_id')->references('id')->on('sh_customer_groups')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('price_list_id')->references('id')->on('sh_price_lists')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('sh_partner_price_list')) {
            Schema::create('sh_partner_price_list', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('price_list_id');
                $table->primary(['partner_id', 'price_list_id']);
                $table->foreign('partner_id')->references('id')->on('sh_partners')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('price_list_id')->references('id')->on('sh_price_lists')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_partner_price_list');
        Schema::dropIfExists('sh_group_price_list');
        Schema::dropIfExists('sh_price_list_items');
        Schema::dropIfExists('sh_price_lists');
        Schema::dropIfExists('sh_partner_tiers');
        Schema::dropIfExists('sh_partner_users');
        Schema::dropIfExists('sh_partners');
        Schema::dropIfExists('sh_customer_group_user');
        Schema::dropIfExists('sh_customer_groups');
    }
};
