<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Ensure at least one currency exists
            $currencyId = DB::table('currencies')->value('id');
            if (! $currencyId) {
                return;
            }

            $priceListId = DB::table('price_lists')->where('name', 'B2B EUR Net')->value('id');
            if (! $priceListId) {
                $priceListId = DB::table('price_lists')->insertGetId([
                    'name' => 'B2B EUR Net',
                    'currency_id' => $currencyId,
                    'zone_id' => null,
                    'priority' => 50,
                    'is_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Attach to Wholesale group if exists
            $groupId = DB::table('customer_groups')->where('code', 'wholesale')->value('id');
            if ($groupId) {
                $exists = DB::table('group_price_list')->where('group_id', $groupId)->where('price_list_id', $priceListId)->exists();
                if (! $exists) {
                    DB::table('group_price_list')->insert([
                        'group_id' => $groupId,
                        'price_list_id' => $priceListId,
                    ]);
                }
            }

            // Create net prices for up to 50 products
            $productIds = DB::table('products')->inRandomOrder()->limit(50)->pluck('id')->all();
            foreach ($productIds as $pid) {
                $has = DB::table('price_list_items')->where('price_list_id', $priceListId)->where('product_id', $pid)->exists();
                if ($has) {
                    continue;
                }

                $basePrice = DB::table('prices')->where('priceable_type', 'Product')->where('priceable_id', $pid)->value('amount');
                if ($basePrice === null) {
                    $basePrice = 50.00;
                }
                $net = max(1, round($basePrice * 0.85, 2));
                DB::table('price_list_items')->insert([
                    'price_list_id' => $priceListId,
                    'product_id' => $pid,
                    'variant_id' => null,
                    'net_amount' => $net,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
