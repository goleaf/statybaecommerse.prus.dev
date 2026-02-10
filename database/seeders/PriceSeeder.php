<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;

final class PriceSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currency = Currency::query()
            ->where('is_default', true)
            ->first()
            ?? Currency::query()->where('code', 'EUR')->first()
            ?? Currency::factory()->create([
                'code'       => 'EUR',
                'symbol'     => '€',
                'is_default' => true,
                'is_active'  => true,
                'is_enabled' => true,
            ]);

        $products = Product::query()->limit(16)->get();

        if ($products->isEmpty()) {
            $products = Product::factory()->count(16)->create();
        }

        $types = ['retail', 'sale', 'wholesale', 'special'];

        foreach ($products as $index => $product) {
            $amount = fake()->randomFloat(2, 8, 750);
            $costAmount = fake()->boolean(40) ? $amount * fake()->randomFloat(2, 0.5, 0.9) : null;
            $startsAt = now()->subDays(fake()->numberBetween(1, 45));
            $endsAt = fake()->boolean(30) ? now()->addDays(fake()->numberBetween(7, 90)) : null;

            Price::query()->updateOrCreate(
                [
                    'priceable_type' => $product->getMorphClass(),
                    'priceable_id'   => $product->getKey(),
                    'currency_id'    => $currency->getKey(),
                    'type'           => $types[$index % count($types)],
                ],
                [
                    'amount'      => round($amount, 2),
                    'cost_amount' => $costAmount !== null ? round($costAmount, 2) : null,
                    'starts_at'   => $startsAt,
                    'ends_at'     => $endsAt,
                    'is_enabled'  => true,
                    'metadata'    => [
                        'seeded' => true,
                        'source' => self::class,
                    ],
                ],
            );
        }
    }
}
