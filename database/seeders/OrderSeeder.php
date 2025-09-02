<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = \App\Models\User::query()->first();
            if (!$user) {
                $user = \App\Models\User::factory()->create();
            }

            $currency = \Shop\Core\Models\Currency::query()->where('code', current_currency())->first()
                ?: \Shop\Core\Models\Currency::query()->where('id', (int) (string) shopper_setting('default_currency_id'))->first();

            $zone = \Shop\Core\Models\Zone::query()->first();
            $carrier = \Shop\Core\Models\Carrier::query()->first();
            $payment = \Shop\Core\Models\PaymentMethod::query()->first();

            if (!$currency || !$zone || !$carrier || !$payment) {
                return;
            }

            $products = \App\Models\Product::query()->where('is_visible', true)->whereNotNull('published_at')->inRandomOrder()->limit(5)->get();
            if ($products->isEmpty()) {
                return;
            }

            /** @var \Shop\Core\Models\Order $order */
            $order = \Shop\Core\Models\Order::query()->create([
                'number' => 'WEB-' . Str::upper(Str::random(8)),
                'currency_code' => $currency->code,
                'channel_id' => \Shop\Core\Models\Channel::query()->value('id'),
                'zone_id' => $zone->id,
                'payment_method_id' => $payment->id,
                'customer_id' => $user->id,
            ]);

            foreach ($products as $p) {
                $amountCents = (int) round((optional($p->prices()->where('currency_id', $currency->id)->first())->amount ?? random_int(1000, 5000) / 100) * 100);
                $order->items()->create([
                    'product_id' => $p->id,
                    'product_type' => \App\Models\Product::class,
                    'unit_price_amount' => $amountCents,
                    'quantity' => random_int(1, 2),
                    'name' => $p->name,
                    'sku' => $p->sku ?? 'SKU-' . Str::upper(Str::random(6)),
                ]);
            }

            $order->shippingAddress()->create([
                'customer_id' => $user->id,
                'last_name' => 'Jonaitis',
                'first_name' => 'Jonas',
                'street_address' => 'Didžioji g. 1',
                'postal_code' => '01128',
                'city' => 'Vilnius',
                'phone' => '+37060000000',
                'country_name' => 'Lithuania',
            ]);
            $order->billingAddress()->create([
                'customer_id' => $user->id,
                'last_name' => 'Jonaitis',
                'first_name' => 'Jonas',
                'street_address' => 'Didžioji g. 1',
                'postal_code' => '01128',
                'city' => 'Vilnius',
                'phone' => '+37060000000',
                'country_name' => 'Lithuania',
            ]);

            // totals
            $subtotalCents = (int) $order->items()->get()->sum(fn($i) => (int) $i->unit_price_amount * (int) $i->quantity);
            $order->update([
                'price_amount' => $subtotalCents,
                'subtotal_amount' => round($subtotalCents / 100, 2),
                'shipping_total_amount' => 9.99,
                'grand_total_amount' => round($subtotalCents / 100, 2) + 9.99,
            ]);

            DB::table('sh_order_shippings')->insert([
                'order_id' => $order->id,
                'carrier_name' => $carrier->name ?? 'Carrier',
                'tracking_number' => null,
                'tracking_url' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
