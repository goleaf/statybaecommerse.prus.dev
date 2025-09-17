<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExtendedDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Brands
            \App\Models\Brand::factory()->count(12)->create();

            // Categories (roots + children)
            $roots = \App\Models\Category::factory()->count(10)->create();
            foreach ($roots as $root) {
                \App\Models\Category::factory()->count(random_int(1, 3))->create(['parent_id' => $root->id]);
            }

            // Collections (mix manual/auto)
            $collections = \App\Models\Collection::factory()->count(8)->create();

            // Attributes + values
            $attributes = \App\Models\Attribute::factory()->count(5)->create();
            foreach ($attributes as $attr) {
                \App\Models\AttributeValue::factory()->count(random_int(5, 12))->create(['attribute_id' => $attr->id]);
            }

            // Products
            $products = \App\Models\Product::factory()->count(150)->create()->each(function ($product) use ($collections) {
                // brand
                $brandId = \App\Models\Brand::query()->inRandomOrder()->value('id');
                if ($brandId) {
                    $product->brand_id = $brandId;
                    $product->save();
                }

                // categories
                $catIds = \App\Models\Category::query()->inRandomOrder()->limit(random_int(1, 3))->pluck('id')->all();
                if (! empty($catIds)) {
                    $product->categories()->syncWithoutDetaching($catIds);
                }

                // manual collections
                $manualCollections = $collections->where('type', 'manual')->random($collections->where('type', 'manual')->count() > 0 ? min(2, $collections->where('type', 'manual')->count()) : 0);
                foreach ($manualCollections as $col) {
                    $product->collections()->syncWithoutDetaching([$col->id]);
                }

                // media placeholder if available
                $path = 'demo/tshirt.jpg';
                if (Storage::disk('public')->exists($path)) {
                    $product->addMedia(Storage::disk('public')->path($path))->toMediaCollection(config('media.storage.collection_name'));
                }

                // price base currency
                \App\Models\Price::query()->updateOrCreate([
                    'priceable_type' => $product->getMorphClass(),
                    'priceable_id' => $product->id,
                    'currency_id' => (int) (\App\Models\Currency::query()->where('code', current_currency())->value('id')
                        ?: \App\Models\Currency::query()->where('is_default', true)->value('id')),
                ], [
                    'amount' => random_int(1000, 15000) / 100,
                    'compare_amount' => random_int(0, 1) ? random_int(1100, 18000) / 100 : null,
                    'cost_amount' => random_int(700, 12000) / 100,
                    'is_enabled' => true,
                ]);
            });

            // Ensure a default variant and inventory for every product
            $defaultInventoryId = \App\Models\Location::query()->value('id');
            $defaultCurrencyId = (int) (\App\Models\Currency::query()->where('code', current_currency())->value('id')
                ?: \App\Models\Currency::query()->where('is_default', true)->value('id'));
            foreach ($products as $product) {
                $variant = \App\Models\ProductVariant::query()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'name' => 'Default',
                    ],
                    [
                        'sku' => strtoupper(Str::random(12)),
                        'allow_backorder' => false,
                        'status' => 'active',
                    ]
                );

                // Variant price mirrors product price
                $productPrice = \App\Models\Price::query()
                    ->where('priceable_type', $product->getMorphClass())
                    ->where('priceable_id', $product->id)
                    ->where('currency_id', $defaultCurrencyId)
                    ->first();

                if ($productPrice) {
                    \App\Models\Price::query()->updateOrCreate([
                        'priceable_type' => $variant->getMorphClass(),
                        'priceable_id' => $variant->id,
                        'currency_id' => $defaultCurrencyId,
                    ], [
                        'amount' => $productPrice->amount,
                        'compare_amount' => $productPrice->compare_amount,
                        'cost_amount' => $productPrice->cost_amount,
                        'is_enabled' => true,
                    ]);
                }

                if ($defaultInventoryId) {
                    DB::table('variant_inventories')->upsert([
                        [
                            'variant_id' => $variant->id,
                            'location_id' => (int) $defaultInventoryId,
                            'stock' => random_int(5, 50),
                            'reserved' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ], ['variant_id', 'location_id'], ['stock', 'reserved', 'updated_at']);
                }
            }

            // Reviews (only if table and expected columns exist)
            if (\Illuminate\Support\Facades\Schema::hasTable('reviews') && \Illuminate\Support\Facades\Schema::hasColumn('reviews', 'rating')) {
                $productIds = \App\Models\Product::query()->pluck('id')->all();
                if (! empty($productIds)) {
                    $userId = \App\Models\User::query()->inRandomOrder()->value('id');
                    if (! $userId) {
                        $userId = \App\Models\User::factory()->create()->id;
                    }
                    $now = now();
                    $rows = [];
                    for ($i = 0; $i < 300; $i++) {
                        $randomProductId = $productIds[array_rand($productIds)];
                        $row = [
                            'title' => 'Review #'.($i + 1),
                            'content' => 'Demo review content #'.($i + 1),
                            'rating' => random_int(1, 5),
                            'is_approved' => (bool) random_int(0, 1),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        if (\Illuminate\Support\Facades\Schema::hasColumn('reviews', 'user_id')) {
                            $row['user_id'] = $userId;
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('reviews', 'product_id')) {
                            $row['product_id'] = $randomProductId;
                        }
                        $rows[] = $row;
                    }
                    DB::table('reviews')->insert($rows);
                }
            }

            // Demo addresses and orders
            $user = \App\Models\User::query()->first();
            if ($user) {
                $address = $user->addresses()->create([
                    'type' => 'shipping',
                    'last_name' => 'Doe',
                    'first_name' => 'John',
                    'company' => null,
                    'address_line_1' => '123 Main St',
                    'address_line_2' => null,
                    'postal_code' => '00000',
                    'city' => 'Springfield',
                    'phone' => '1234567890',
                    'is_default' => true,
                    'country' => 'LT',
                ]);

                $currency = \App\Models\Currency::query()->where('code', current_currency())->first()
                    ?: \App\Models\Currency::query()->where('is_default', true)->first();
                $zone = \App\Models\Zone::query()->first();

                $productsForOrder = \App\Models\Product::query()->where('is_visible', true)->whereNotNull('published_at')->limit(3)->get();
                if ($productsForOrder->isNotEmpty() && $currency && $zone && $carrier && $payment) {
                    $order = \App\Models\Order::query()->create([
                        'number' => 'WEB-'.Str::upper(Str::random(8)),
                        'currency' => $currency->code,
                        'channel_id' => \App\Models\Channel::query()->value('id'),
                        'zone_id' => $zone->id,
                        'user_id' => $user->id,
                        'payment_method' => 'cash_on_delivery',
                        'payment_status' => 'pending',
                        'warehouse' => null,
                    ]);

                    foreach ($productsForOrder as $p) {
                        $amount = (float) (optional($p->prices()->whereHas('currency', fn ($q) => $q->where('code', $currency->code))->first())->amount ?? (random_int(1000, 5000) / 100));
                        $order->items()->create([
                            'product_id' => $p->id,
                            'name' => $p->name,
                            'sku' => $p->sku ?? 'SKU-'.Str::upper(Str::random(6)),
                            'unit_price' => $amount,
                            'quantity' => 1,
                            'total' => $amount,
                        ]);
                    }

                    $order->shipping()->create([
                        'carrier' => 'standard',
                        'service' => 'ground',
                        'price' => 9.99,
                        'weight' => 1.0,
                        'tracking_number' => null,
                        'estimated_delivery_date' => now()->addDays(5),
                    ]);

                    $order->update([
                        'subtotal' => $order->items()->sum('total'),
                        'shipping_amount' => 9.99,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'total' => $order->items()->sum('total') + 9.99,
                    ]);

                    // totals already updated above
                }
            }
        });
    }
}
