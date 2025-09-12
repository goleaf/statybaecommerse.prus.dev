<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class EnsureBrandProductsSeeder extends Seeder
{
    private const TARGET_PER_BRAND = 100;

    private const TARGET_PER_CATEGORY = 100;

    public function run(): void
    {
        $this->ensureProductsPerBrand();
        $this->ensureProductsPerCategory();
    }

    private function ensureProductsPerBrand(): void
    {
        $categoryIds = Category::query()->pluck('id');

        Brand::query()->select(['id'])->orderBy('id')->chunkById(100, function (Collection $brands) use ($categoryIds): void {
            foreach ($brands as $brand) {
                $current = Product::query()->where('brand_id', $brand->id)->count();
                $missing = max(0, self::TARGET_PER_BRAND - $current);

                if ($missing === 0) {
                    continue;
                }

                $products = Product::factory()
                    ->count($missing)
                    ->create([
                        'brand_id' => $brand->id,
                        'is_visible' => true,
                        'published_at' => now(),
                    ]);

                if ($categoryIds->isNotEmpty()) {
                    foreach ($products as $product) {
                        $attach = $categoryIds->random(min(3, $categoryIds->count()))->all();
                        $product->categories()->syncWithoutDetaching($attach);
                    }
                }
            }
        });
    }

    private function ensureProductsPerCategory(): void
    {
        Category::query()->select(['id'])->orderBy('id')->chunkById(100, function (Collection $categories): void {
            foreach ($categories as $category) {
                $currentCount = $category->products()->count();

                if ($currentCount >= self::TARGET_PER_CATEGORY) {
                    continue;
                }

                $needed = self::TARGET_PER_CATEGORY - $currentCount;

                // Attach existing products not yet linked
                $already = $category->products()->pluck('products.id')->all();
                $pool = Product::query()
                    ->whereNotIn('id', $already)
                    ->inRandomOrder()
                    ->limit($needed)
                    ->pluck('id')
                    ->all();

                if (! empty($pool)) {
                    $category->products()->syncWithoutDetaching($pool);
                    $needed -= count($pool);
                }

                if ($needed <= 0) {
                    continue;
                }

                // Create extra products and attach to this category
                $brandId = Brand::query()->inRandomOrder()->value('id');
                $created = Product::factory()
                    ->count($needed)
                    ->create([
                        'brand_id' => $brandId,
                        'is_visible' => true,
                        'published_at' => now(),
                    ]);

                $category->products()->syncWithoutDetaching($created->pluck('id')->all());
            }
        });
    }
}
