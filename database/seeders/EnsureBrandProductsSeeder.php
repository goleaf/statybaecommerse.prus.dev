<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class EnsureBrandProductsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id');

        Brand::query()->chunk(100, function ($brands) use ($categories) {
            foreach ($brands as $brand) {
                $hasProduct = Product::query()->where('brand_id', $brand->id)->exists();
                if ($hasProduct) {
                    continue;
                }

                $product = Product::factory()->create([
                    'brand_id' => $brand->id,
                    'is_visible' => true,
                    'published_at' => now(),
                ]);

                if ($categories->isNotEmpty()) {
                    $product->categories()->sync([$categories->random()]);
                }
            }
        });
    }
}

