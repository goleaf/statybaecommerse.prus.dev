<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablesToRename = [
            'sh_attribute_product' => 'attribute_product',
            'sh_attribute_value_product_variant' => 'attribute_value_product_variant',
            'sh_brands' => 'brands_legacy', // Will be handled separately
            'sh_carrier_options' => 'carrier_options',
            'sh_carriers' => 'carriers',
            'sh_categories' => 'categories_legacy', // Will be handled separately
            'sh_collections' => 'collections_legacy', // Will be handled separately
            'sh_discountables' => 'discountables',
            'sh_inventory_histories' => 'inventory_histories',
            'sh_order_addresses' => 'order_addresses',
            'sh_order_items' => 'order_items_legacy', // Will be handled separately
            'sh_order_refunds' => 'order_refunds',
            'sh_order_shipping' => 'order_shipping_legacy', // Will be handled separately
            'sh_orders' => 'orders_legacy', // Will be handled separately
            'sh_payment_methods' => 'payment_methods',
            'sh_product_has_relations' => 'product_has_relations',
            'sh_products' => 'products_legacy', // Will be handled separately
            'sh_reviews' => 'reviews_legacy', // Will be handled separately
            'sh_settings' => 'settings',
            'sh_user_addresses' => 'user_addresses',
            'sh_users_geolocation_history' => 'users_geolocation_history',
            'sh_zone_has_relations' => 'zone_has_relations',
        ];

        foreach ($tablesToRename as $oldName => $newName) {
            if (Schema::hasTable($oldName) && !Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
                echo "Renamed table: {$oldName} -> {$newName}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablesToRevert = [
            'attribute_product' => 'sh_attribute_product',
            'attribute_value_product_variant' => 'sh_attribute_value_product_variant',
            'brands_legacy' => 'sh_brands',
            'carrier_options' => 'sh_carrier_options',
            'carriers' => 'sh_carriers',
            'categories_legacy' => 'sh_categories',
            'collections_legacy' => 'sh_collections',
            'discountables' => 'sh_discountables',
            'inventory_histories' => 'sh_inventory_histories',
            'order_addresses' => 'sh_order_addresses',
            'order_items_legacy' => 'sh_order_items',
            'order_refunds' => 'sh_order_refunds',
            'order_shipping_legacy' => 'sh_order_shipping',
            'orders_legacy' => 'sh_orders',
            'payment_methods' => 'sh_payment_methods',
            'product_has_relations' => 'sh_product_has_relations',
            'products_legacy' => 'sh_products',
            'reviews_legacy' => 'sh_reviews',
            'settings' => 'sh_settings',
            'user_addresses' => 'sh_user_addresses',
            'users_geolocation_history' => 'sh_users_geolocation_history',
            'zone_has_relations' => 'sh_zone_has_relations',
        ];

        foreach ($tablesToRevert as $newName => $oldName) {
            if (Schema::hasTable($newName) && !Schema::hasTable($oldName)) {
                Schema::rename($newName, $oldName);
            }
        }
    }
};
