<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Price;
use App\Models\PriceList;

final class CurrencySeeder extends BaseSeeder
{
    public function run(): void
    {
        $attributes = Currency::factory()
            ->eur()
            ->default()
            ->active()
            ->state(['sort_order' => 0])
            ->raw();

        $euro = Currency::query()->updateOrCreate(
            ['code' => 'EUR'],
            $attributes,
        );

        Price::query()
            ->where('currency_id', '!=', $euro->getKey())
            ->update(['currency_id' => $euro->getKey()]);

        PriceList::query()
            ->where('currency_id', '!=', $euro->getKey())
            ->update(['currency_id' => $euro->getKey()]);

        Currency::query()
            ->where('id', '!=', $euro->getKey())
            ->update([
                'is_enabled' => false,
                'is_active'  => false,
                'is_default' => false,
            ]);

        Currency::query()
            ->whereKey($euro->getKey())
            ->update([
                'is_enabled' => true,
                'is_active'  => true,
                'is_default' => true,
            ]);
    }
}
