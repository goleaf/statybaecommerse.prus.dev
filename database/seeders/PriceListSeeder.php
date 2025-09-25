<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

final class PriceListSeeder extends Seeder
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
