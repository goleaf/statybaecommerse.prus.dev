<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantPriceHistory;
use Illuminate\Database\Seeder;

final class VariantPriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::factory()->count(10)->create();

        $variants->each(function (ProductVariant $variant): void {
            VariantPriceHistory::factory()
                ->count(fake()->numberBetween(3, 5))
                ->for($variant)
                ->create();
        });

        $this->command->info('Created variant price history records.');
    }
}
