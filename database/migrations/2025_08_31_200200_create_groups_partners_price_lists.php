<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sh_customer_groups')) {
            Schema::create('sh_customer_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_customer_group_user')) {
            Schema::create('sh_customer_group_user', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('user_id');
                $table->primary(['group_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('sh_partners')) {
            Schema::create('sh_partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->enum('tier', ['gold', 'silver', 'bronze', 'custom'])->default('custom');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_partner_users')) {
            Schema::create('sh_partner_users', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('user_id');
                $table->primary(['partner_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('sh_partner_tiers')) {
            Schema::create('sh_partner_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('priority')->default(100);
                $table->decimal('default_discount_pct', 5, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_price_lists')) {
            Schema::create('sh_price_lists', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('currency_id');
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedInteger('priority')->default(100);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_price_list_items')) {
            Schema::create('sh_price_list_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('price_list_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->decimal('net_amount', 12, 2);
                $table->timestamps();

                $table->index(['price_list_id']);
                $table->index(['product_id']);
                $table->index(['variant_id']);
            });
        }

        // optional links price lists to groups/partners
        if (! Schema::hasTable('sh_group_price_list')) {
            Schema::create('sh_group_price_list', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('price_list_id');
                $table->primary(['group_id', 'price_list_id']);
            });
        }

        if (! Schema::hasTable('sh_partner_price_list')) {
            Schema::create('sh_partner_price_list', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('price_list_id');
                $table->primary(['partner_id', 'price_list_id']);
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
