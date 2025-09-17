<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tablesToRename = [
        'sh_addresses' => 'addresses',
        'sh_attributes' => 'attributes',
        'sh_attribute_values' => 'attribute_values',
        'sh_attribute_translations' => 'attribute_translations',
        'sh_attribute_value_translations' => 'attribute_value_translations',
        'sh_brand_translations' => 'brand_translations',
        'sh_category_translations' => 'category_translations',
        'sh_channels' => 'channels',
        'sh_collection_translations' => 'collection_translations',
        'sh_collection_rules' => 'collection_rules',
        'sh_countries' => 'countries',
        'sh_country_zone' => 'country_zone',
        'sh_currencies' => 'currencies',
        'sh_customer_groups' => 'customer_groups',
        'sh_customer_group_user' => 'customer_group_user',
        'sh_discounts' => 'discounts',
        'sh_discount_brands' => 'discount_brands',
        'sh_discount_categories' => 'discount_categories',
        'sh_discount_codes' => 'discount_codes',
        'sh_discount_collections' => 'discount_collections',
        'sh_discount_conditions' => 'discount_conditions',
        'sh_discount_customers' => 'discount_customers',
        'sh_discount_customer_groups' => 'discount_customer_groups',
        'sh_discount_products' => 'discount_products',
        'sh_discount_redemptions' => 'discount_redemptions',
        'sh_discount_zones' => 'discount_zones',
        'sh_discount_campaigns' => 'discount_campaigns',
        'sh_campaign_discount' => 'campaign_discount',
        'sh_group_price_list' => 'group_price_list',
        'sh_inventories' => 'inventories',
        'sh_legal_translations' => 'legal_translations',
        'sh_legals' => 'legals',
        'sh_locations' => 'locations',
        'sh_order_items' => 'order_items',
        'sh_orders' => 'orders',
        'sh_order_shippings' => 'order_shippings',
        'sh_partners' => 'partners',
        'sh_partner_price_list' => 'partner_price_list',
        'sh_partner_tiers' => 'partner_tiers',
        'sh_partner_users' => 'partner_users',
        'sh_prices' => 'prices',
        'sh_price_lists' => 'price_lists',
        'sh_price_list_items' => 'price_list_items',
        'sh_products' => 'products',
        'sh_product_attributes' => 'product_attributes',
        'sh_product_translations' => 'product_translations',
        'sh_product_variants' => 'product_variants',
        'sh_product_variant_attributes' => 'product_variant_attributes',
        'sh_variant_inventories' => 'variant_inventories',
    ];

    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->tablesToRename as $oldName => $newName) {
                // Attempt rename; if old table doesn't exist or new exists, database will throw — acceptable in forward-only context if sequence has already applied
                try {
                    Schema::rename($oldName, $newName);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Forward-only rename; no automatic rollback to avoid destructive renames.
    }
};
