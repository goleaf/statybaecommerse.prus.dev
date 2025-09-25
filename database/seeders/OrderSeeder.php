<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create required entities using factories
        $user = User::first() ?? User::factory()->create();
        $currency = Currency::where('code', 'EUR')->first() ?: Currency::factory()->eur()->default()->create();
        $zone = Zone::first() ?: Zone::factory()->create();
        $channel = Channel::first() ?: Channel::factory()->create();

        $visibleProducts = Product::where('is_visible', true)
            ->whereNotNull('published_at')
            ->inRandomOrder()
            ->limit(50)
            ->get();

        if ($visibleProducts->isEmpty()) {
            $visibleProducts = Product::factory()
                ->count(10)
                ->state(['is_visible' => true, 'published_at' => now()])
                ->create();
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
                // Create order using factory with relationships
                /** @var Order $order */
                $order = Order::factory()
                    ->for($user)
                    ->for($channel)
                    ->for($zone)
                    ->state([
                        'number' => 'WEB-'.Str::upper(Str::random(8)),
                        'currency' => $currency->code,
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'created_at' => $config['date']->copy()->addDays(random_int(0, 3)),
                        'updated_at' => now(),
                    ])
                    ->create();

                // Create order items using factory relationships
                $items = $visibleProducts->random(min(random_int(1, 4), $visibleProducts->count()));
                $subtotal = 0.0;

                foreach ($items as $product) {
                    $unitPrice = (float) (optional($product->prices()->whereHas('currency', fn ($q) => $q->where('code', $currency->code))->first())->amount ?? (random_int(1000, 5000) / 100));
                    $quantity = random_int(1, 3);
                    $lineTotal = $unitPrice * $quantity;
                    $subtotal += $lineTotal;

                    $order->items()->save(
                        OrderItem::factory()
                            ->for($product)
                            ->state([
                                'name' => $product->name,
                                'sku' => $product->sku ?? 'SKU-'.Str::upper(Str::random(6)),
                                'unit_price' => $unitPrice,
                                'quantity' => $quantity,
                                'total' => $lineTotal,
                            ])
                            ->make()
                    );
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

                // Create shipping using factory relationship
                $order->shipping()->save(
                    OrderShipping::factory()
                        ->state([
                            'carrier_name' => 'standard',
                            'service' => 'ground',
                            'cost' => $shippingCost,
                            'weight' => 1.0,
                            'tracking_number' => null,
                            'tracking_url' => null,
                            'shipped_at' => $config['date']->copy()->addDays(random_int(1, 5)),
                            'estimated_delivery' => $config['date']->copy()->addDays(7),
                        ])
                        ->make()
                );
            }
        }
    }
}
