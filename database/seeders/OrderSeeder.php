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

            $currency = \App\Models\Currency::query()->where('code', current_currency())->first()
                ?: \App\Models\Currency::query()->where('is_default', true)->first();

            $zone = \App\Models\Zone::query()->first();

            if (!$currency || !$zone) {
                return;
            }

            $products = \App\Models\Product::query()->where('is_visible', true)->whereNotNull('published_at')->inRandomOrder()->limit(5)->get();
            if ($products->isEmpty()) {
                return;
            }

            /** @var \App\Models\Order $order */
            $order = \App\Models\Order::query()->create([
                'number' => 'WEB-' . Str::upper(Str::random(8)),
                'currency' => $currency->code,
                'channel_id' => \App\Models\Channel::query()->value('id'),
                'zone_id' => $zone->id,
                'user_id' => $user->id,
                'payment_method' => 'cash_on_delivery',
                'payment_status' => 'pending',
                'status' => 'confirmed',
            ]);

            foreach ($products as $p) {
                $amount = (float) (optional($p->prices()->whereHas('currency', fn($q) => $q->where('code', $currency->code))->first())->amount ?? (random_int(1000, 5000) / 100));
                $order->items()->create([
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku ?? 'SKU-' . Str::upper(Str::random(6)),
                    'unit_price' => $amount,
                    'quantity' => random_int(1, 2),
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

            // totals
            $order->update([
                'subtotal' => $order->items()->sum('total'),
                'shipping_amount' => 9.99,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => $order->items()->sum('total') + 9.99,
            ]);
        });
    }
}
