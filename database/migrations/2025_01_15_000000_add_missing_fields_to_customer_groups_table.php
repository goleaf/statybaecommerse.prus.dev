<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table) {
            // Add missing fields if they don't exist
            if (!Schema::hasColumn('customer_groups', 'code')) {
                $table->string('code')->unique()->after('name');
            }
            if (!Schema::hasColumn('customer_groups', 'discount_fixed')) {
                $table->decimal('discount_fixed', 10, 2)->default(0)->after('discount_percentage');
            }
            if (!Schema::hasColumn('customer_groups', 'has_special_pricing')) {
                $table->boolean('has_special_pricing')->default(false)->after('discount_fixed');
            }
            if (!Schema::hasColumn('customer_groups', 'has_volume_discounts')) {
                $table->boolean('has_volume_discounts')->default(false)->after('has_special_pricing');
            }
            if (!Schema::hasColumn('customer_groups', 'can_view_prices')) {
                $table->boolean('can_view_prices')->default(true)->after('has_volume_discounts');
            }
            if (!Schema::hasColumn('customer_groups', 'can_place_orders')) {
                $table->boolean('can_place_orders')->default(true)->after('can_view_prices');
            }
            if (!Schema::hasColumn('customer_groups', 'can_view_catalog')) {
                $table->boolean('can_view_catalog')->default(true)->after('can_place_orders');
            }
            if (!Schema::hasColumn('customer_groups', 'can_use_coupons')) {
                $table->boolean('can_use_coupons')->default(true)->after('can_view_catalog');
            }
            if (!Schema::hasColumn('customer_groups', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('can_use_coupons');
            }
            if (!Schema::hasColumn('customer_groups', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('customer_groups', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_default');
            }
            if (!Schema::hasColumn('customer_groups', 'type')) {
                $table->enum('type', ['regular', 'vip', 'wholesale', 'retail', 'corporate'])->default('regular')->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'discount_fixed',
                'has_special_pricing',
                'has_volume_discounts',
                'can_view_prices',
                'can_place_orders',
                'can_view_catalog',
                'can_use_coupons',
                'is_active',
                'is_default',
                'sort_order',
                'type',
            ]);
        });
    }
};
