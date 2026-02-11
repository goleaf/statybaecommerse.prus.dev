<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\VariantCombination;
use Illuminate\Database\Eloquent\Collection;

final class VariantCombinationSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()
            ->withoutGlobalScopes()
            ->doesntHave('variantCombinations')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(50, function (Collection $products): void {
                foreach ($products as $product) {
                    VariantCombination::createCombinationsForProduct($product);
                }
            });
    }
}
