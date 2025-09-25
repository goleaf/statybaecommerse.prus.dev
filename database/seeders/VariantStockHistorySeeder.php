<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantStockHistory;
use Illuminate\Database\Seeder;

class VariantStockHistorySeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::factory()->count(10)->create();

        $variants->each(function (ProductVariant $variant): void {
            VariantStockHistory::factory()
                ->count(fake()->numberBetween(6, 12))
                ->for($variant)
                ->create();
        });

        $this->command->info('VariantStockHistory seeded successfully!');
    }
}
