<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Channel;
use App\Models\Country;
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
        $country = Country::query()->updateOrCreate(
            ['cca2' => 'LT'],
            [
                // Persist a deterministic Lithuanian record so repeated seeding runs do not trip unique ISO/CCA constraints.
                'name'               => 'Lithuania',
                'name_official'      => 'Republic of Lithuania',
                'cca3'               => 'LTU',
                'ccn3'               => '440',
                'code'               => 'lt', // Reuse the lowercase alpha-2 code to align with the dedicated country seeders.
                'iso_code'           => 'LTU',
                'currency_code'      => $currency->code,
                'currency_symbol'    => '€',
                'phone_code'         => '370',
                'phone_calling_code' => '+370',
                'region'             => 'Europe',
                'subregion'          => 'Northern Europe',
                'is_active'          => true,
                'is_enabled'         => true,
            ],
        );

        // Get product IDs only to reduce memory usage
        $visibleProductIds = Product::query()
            ->published()
            ->enabled()
            ->inRandomOrder()
            ->limit(50)
            ->pluck('id')
            ->toArray();

        if (empty($visibleProductIds)) {
            $products = Product::factory()
                ->count(10)
                ->state(['status' => 'published', 'is_enabled' => true, 'published_at' => now()])
                ->create();
            $visibleProductIds = $products->pluck('id')->toArray();
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

        $paymentMethods = PaymentMethod::cases();

        foreach ($ordersToCreate as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                // Create order using factory with relationships
                /** @var Order $order */
                $order = Order::factory()
                    ->for($user)
                    ->for($channel)
                    ->for($zone)
                    ->for($country)
                    ->state([
                        'number'         => 'WEB-' . Str::upper(Str::random(8)),
                        'currency'       => $currency->code,
                        'country_id'     => $country->getKey(),
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)]->value,
                        'payment_status' => 'paid',
                        'status'         => 'processing',
                        'created_at'     => $config['date']->copy()->addDays(random_int(0, 3)),
                        'updated_at'     => now(),
                    ])
                    ->create();

                // Create order items - use only IDs to reduce memory
                $selectedProductIds = array_rand(array_flip($visibleProductIds), min(random_int(1, 4), count($visibleProductIds)));
                if (! is_array($selectedProductIds)) {
                    $selectedProductIds = [$selectedProductIds];
                }

                $subtotal = 0.0;
                $orderItems = [];

                foreach ($selectedProductIds as $productId) {
                    // Get minimal product data
                    $product = Product::select('id', 'name', 'sku')->find($productId);

                    $unitPrice = (float) (random_int(1000, 5000) / 100);
                    $quantity = random_int(1, 3);
                    $lineTotal = $unitPrice * $quantity;
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'order_id'   => $order->getKey(),
                        'product_id' => $product->id,
                        'name'       => $product->name,
                        'sku'        => $product->sku ?? 'SKU-' . Str::upper(Str::random(6)),
                        'unit_price' => $unitPrice,
                        'price'      => $unitPrice,
                        'quantity'   => $quantity,
                        'total'      => $lineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert order items to reduce memory usage
                OrderItem::insert($orderItems);

                $shippingCost = 9.99;
                $taxAmount = round($subtotal * 0.0, 2);
                $discount = 0.0;
                $total = $subtotal + $shippingCost + $taxAmount - $discount;

                $order->update([
                    'subtotal'        => $subtotal,
                    'shipping_amount' => $shippingCost,
                    'tax_amount'      => $taxAmount,
                    'discount_amount' => $discount,
                    'total'           => $total,
                ]);

                // Create shipping using query builder to avoid factory overhead
                OrderShipping::query()->create([
                    'order_id'           => $order->getKey(),
                    'carrier_name'       => 'standard',
                    'carrier'            => 'standard',
                    'shipping_method'    => 'standard',
                    'service'            => 'ground',
                    'service_type'       => 'ground',
                    'cost'               => $shippingCost,
                    'base_cost'          => $shippingCost,
                    'insurance_cost'     => 0.0,
                    'total_cost'         => $shippingCost,
                    'weight'             => 1.0,
                    'tracking_number'    => null,
                    'tracking_url'       => null,
                    'shipped_at'         => $config['date']->copy()->addDays(random_int(1, 5)),
                    'estimated_delivery' => $config['date']->copy()->addDays(7),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // Force garbage collection periodically
                if ($i % 5 === 0) {
                    gc_collect_cycles();
                }
            }
        }
    }
}
