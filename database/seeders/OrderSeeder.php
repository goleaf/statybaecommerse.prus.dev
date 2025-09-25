<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = \App\Models\User::query()->first() ?? \App\Models\User::factory()->create();

            $currency = \App\Models\Currency::query()->where('code', 'EUR')->first()
                ?: \App\Models\Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);

            $zone = \App\Models\Zone::query()->where('currency_id', $currency->id)->first()
                ?: \App\Models\Zone::factory()->create(['currency_id' => $currency->id]);
            
            $channelId = \App\Models\Channel::query()->value('id')
                ?: \App\Models\Channel::factory()->create()->id;

            $visibleProducts = \App\Models\Product::query()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->inRandomOrder()
                ->limit(50)
                ->get();

            if ($visibleProducts->isEmpty()) {
                $visibleProducts = \App\Models\Product::factory()
                    ->count(10)
                    ->create(['is_visible' => true, 'published_at' => now()]);
            }

            // Create a mix of paid orders across current and previous month
            $ordersToCreate = [
                // current month
                ['count' => 6, 'date' => now()->subDays(0)],
                ['count' => 6, 'date' => now()->subDays(7)],
                // previous month
                ['count' => 6, 'date' => now()->subMonth()->addDays(5)],
                ['count' => 6, 'date' => now()->subMonth()->addDays(15)],
            ];

            foreach ($ordersToCreate as $config) {
                for ($i = 0; $i < $config['count']; $i++) {
                    // Create order using factory
                    $order = \App\Models\Order::factory()->create([
                        'number' => 'WEB-'.Str::upper(Str::random(8)),
                        'currency' => $currency->code,
                        'channel_id' => $channelId,
                        'zone_id' => $zone->id,
                        'user_id' => $user->id,
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'created_at' => $config['date']->copy()->addDays(random_int(0, 3)),
                        'updated_at' => now(),
                    ]);

                    // Create order items using factory
                    $items = $visibleProducts->random(min(random_int(1, 4), $visibleProducts->count()));
                    $subtotal = 0.0;
                    
                    foreach ($items as $p) {
                        $unit = (float) (optional($p->prices()->whereHas('currency', fn ($q) => $q->where('code', $currency->code))->first())->amount ?? (random_int(1000, 5000) / 100));
                        $qty = random_int(1, 3);
                        $lineTotal = $unit * $qty;
                        $subtotal += $lineTotal;
                        
                        \App\Models\OrderItem::factory()->create([
                            'order_id' => $order->id,
                            'product_id' => $p->id,
                            'name' => $p->name,
                            'sku' => $p->sku ?? 'SKU-'.Str::upper(Str::random(6)),
                            'unit_price' => $unit,
                            'quantity' => $qty,
                            'total' => $lineTotal,
                        ]);
                    }

                    $shippingCost = 9.99;
                    $taxAmount = round($subtotal * 0.21, 2);
                    $discount = 0.0;
                    $total = $subtotal + $shippingCost + $taxAmount - $discount;

                    $order->update([
                        'subtotal' => $subtotal,
                        'shipping_amount' => $shippingCost,
                        'tax_amount' => $taxAmount,
                        'discount_amount' => $discount,
                        'total' => $total,
                    ]);

                    // Create shipping using factory
                    \App\Models\OrderShipping::factory()->create([
                        'order_id' => $order->id,
                        'carrier_name' => 'standard',
                        'service' => 'ground',
                        'cost' => $shippingCost,
                        'weight' => 1.0,
                        'tracking_number' => null,
                        'tracking_url' => null,
                        'shipped_at' => $config['date']->copy()->addDays(random_int(1, 5)),
                        'estimated_delivery' => $config['date']->copy()->addDays(7),
                    ]);
                }
            }
        });
    }
}
