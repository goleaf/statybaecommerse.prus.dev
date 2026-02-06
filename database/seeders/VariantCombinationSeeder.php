<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\VariantCombination;

class VariantCombinationSeeder extends \Database\Seeders\BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()
            ->count(3)
            ->create()
            ->each(function (Product $product): void {
                VariantCombination::factory()
                    ->count(9)
                    ->forProduct($product)
                    ->create();
            });

        $this->command->info('VariantCombination seeding completed successfully!');
    }
}
