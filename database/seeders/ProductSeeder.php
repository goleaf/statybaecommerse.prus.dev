<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;

final class ProductSeeder extends BaseSeeder
{
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $count = $this->seedFastModeEnabled()
            ? $this->seedFastInt('product_limit', 24, 6)
            : 60;

        Product::factory()
            ->count($count)
            ->published()
            ->create();

        $this->command?->info("ProductSeeder: created {$count} products.");
    }
}
