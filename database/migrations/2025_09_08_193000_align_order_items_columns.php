<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'name') && Schema::hasColumn('order_items', 'product_name')) {
                $table->renameColumn('product_name', 'name');
            }
            if (! Schema::hasColumn('order_items', 'sku') && Schema::hasColumn('order_items', 'product_sku')) {
                $table->renameColumn('product_sku', 'sku');
            }
            if (! Schema::hasColumn('order_items', 'unit_price') && Schema::hasColumn('order_items', 'price')) {
                $table->renameColumn('price', 'unit_price');
            }
            if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('order_items', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_name') && Schema::hasColumn('order_items', 'name')) {
                $table->renameColumn('name', 'product_name');
            }
            if (! Schema::hasColumn('order_items', 'product_sku') && Schema::hasColumn('order_items', 'sku')) {
                $table->renameColumn('sku', 'product_sku');
            }
            if (! Schema::hasColumn('order_items', 'price') && Schema::hasColumn('order_items', 'unit_price')) {
                $table->renameColumn('unit_price', 'price');
            }
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->dropColumn('product_variant_id');
            }
            if (Schema::hasColumn('order_items', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
