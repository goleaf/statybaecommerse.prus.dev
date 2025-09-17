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
                ?: \App\Models\Currency::query()->where('is_default', true)->first();

            $zone = null;
            $channelId = \App\Models\Channel::query()->value('id');

            if (! $currency || ! $zone || ! $channelId) {
                return;
            }

            $visibleProducts = \App\Models\Product::query()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->inRandomOrder()
                ->limit(50)
                ->get();

            if ($visibleProducts->isEmpty()) {
                return;
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
                    /** @var \App\Models\Order $order */
                    $order = \App\Models\Order::query()->create([
                        'number' => 'WEB-'.Str::upper(Str::random(8)),
                        'currency' => $currency->code,
                        'channel_id' => $channelId,
                        'zone_id' => null,
                        'user_id' => $user->id,
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'created_at' => $config['date']->copy()->addDays(random_int(0, 3)),
                        'updated_at' => now(),
                    ]);

                    // 1-4 items per order
                    $items = $visibleProducts->random(min(random_int(1, 4), $visibleProducts->count()));
                    $subtotal = 0.0;
                    foreach ($items as $p) {
                        $resolvedName = is_array($p->name)
                            ? ($p->name['lt'] ?? ($p->name['en'] ?? reset($p->name)))
                            : $p->name;
                        $unit = (float) (optional($p->prices()->whereHas('currency', fn ($q) => $q->where('code', $currency->code))->first())->amount ?? (random_int(1000, 5000) / 100));
                        $qty = random_int(1, 3);
                        $lineTotal = $unit * $qty;
                        $subtotal += $lineTotal;
                        $order->items()->create([
                            'product_id' => $p->id,
                            'name' => $resolvedName,
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

                    $order->shipping()->create([
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
