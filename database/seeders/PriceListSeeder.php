<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;

final class PriceListSeeder extends \Database\Seeders\BaseSeeder
{
    public function run(): void
    {
        // Get or create currency
        $currency = Currency::where('code', 'EUR')->first();
        if (! $currency) {
            $currency = Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);
        }
    }
}
