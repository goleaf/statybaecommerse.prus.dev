<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use App\Services\Images\LocalImageGeneratorService;
use Database\Seeders\Data\HouseBuilderCollections;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionProductSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    public function run(): void
    {
        $definitions = HouseBuilderCollections::collections();
        $locales = $this->supportedLocales();

        foreach ($definitions as $slug => $definition) {
            /** @var Collection|null $collection */
            $collection = Collection::where('slug', $slug)->first();

            if (! $collection) {
                $this->command?->warn('CollectionProductSeeder: missing collection "'.$slug.'".');

                continue;
            }

            $categoryIds = Category::whereIn('slug', $definition['categories'] ?? [])->pluck('id')->all();

            foreach ($definition['products'] ?? [] as $productDefinition) {
                $product = $this->createOrUpdateProduct($collection, $productDefinition, $categoryIds, $locales);
                $collection->products()->syncWithoutDetaching([$product->id]);
            }

            $targetCount = max(count($definition['products'] ?? []), 8);
            $this->topUpCollectionWithExistingProducts($collection, $targetCount);

            $this->command?->info('CollectionProductSeeder: populated "'.$collection->name.'" with curated products.');
        }
    }

    private function createOrUpdateProduct(Collection $collection, array $productDefinition, array $collectionCategoryIds, array $locales): Product
    {
        $translations = $productDefinition['translations'];
        $english = $translations['en'];

        $brand = $this->ensureBrand($productDefinition['brand'] ?? 'Statybae Essentials');

        // Check if product already exists to maintain idempotency
        $existingProduct = Product::where('slug', $productDefinition['slug'])->first();
        
        $productData = [
            'type' => 'simple',
            'name' => $english['name'],
            'sku' => $productDefinition['sku'] ?? strtoupper(Str::random(10)),
            'description' => $english['description'],
            'short_description' => $english['short_description'],
            'price' => $productDefinition['price'],
            'sale_price' => $productDefinition['sale_price'] ?? null,
            'stock_quantity' => $productDefinition['stock'] ?? 50,
            'low_stock_threshold' => $productDefinition['low_stock_threshold'] ?? 8,
            'weight' => $productDefinition['weight'] ?? 5.0,
            'length' => $productDefinition['length'] ?? 40.0,
            'width' => $productDefinition['width'] ?? 30.0,
            'height' => $productDefinition['height'] ?? 20.0,
            'is_visible' => true,
            'is_featured' => $productDefinition['featured'] ?? false,
            'manage_stock' => true,
            'status' => 'published',
            'published_at' => $productDefinition['published_at'] ?? now()->subDays(random_int(5, 45)),
            'seo_title' => $english['name'].' - '.config('app.name'),
            'seo_description' => $english['short_description'],
        ];

        if ($existingProduct) {
            $existingProduct->update($productData);
            $product = $existingProduct;
        } else {
            // Use factory to create product with relationships
            $product = Product::factory()
                ->for($brand)
                ->state(array_merge($productData, [
                    'slug' => $productDefinition['slug'],
                ]))
                ->create();
        }

        $productCategorySlugs = $productDefinition['categories'] ?? [];
        $categoryIds = empty($productCategorySlugs)
            ? $collectionCategoryIds
            : Category::whereIn('slug', $productCategorySlugs)->pluck('id')->all();

        if (! empty($categoryIds)) {
            $product->categories()->syncWithoutDetaching($categoryIds);
        }

        foreach ($locales as $locale) {
            $localeTranslation = $translations[$locale] ?? $english;

            $existingTranslation = ProductTranslation::where([
                'product_id' => $product->id,
                'locale' => $locale,
            ])->first();

            $translationData = [
                'name' => $localeTranslation['name'],
                'slug' => Str::slug($localeTranslation['name'].'-'.$locale),
                'summary' => $localeTranslation['short_description'],
                'short_description' => $localeTranslation['short_description'],
                'description' => $localeTranslation['description'],
                'seo_title' => $localeTranslation['name'].' - '.config('app.name'),
                'seo_description' => $localeTranslation['short_description'],
                'meta_keywords' => [],
            ];

            if ($existingTranslation) {
                $existingTranslation->update($translationData);
            } else {
                ProductTranslation::factory()
                    ->for($product)
                    ->state(array_merge($translationData, [
                        'locale' => $locale,
                    ]))
                    ->create();
            }
        }

        $collection->products()->syncWithoutDetaching([$product->id]);

        $firstCategoryName = Category::find($categoryIds[0] ?? null)?->name ?? $collection->name;
        $this->ensureProductImage($product, $productDefinition['image_text'] ?? $english['name'], $firstCategoryName);

        return $product;
    }

    private function topUpCollectionWithExistingProducts(Collection $collection, int $targetCount): void
    {
        $currentCount = $collection->products()->count();

        if ($currentCount >= $targetCount) {
            return;
        }

        $additional = Product::published()
            ->whereNotIn('id', $collection->products()->pluck('products.id'))
            ->orderByDesc('published_at')
            ->limit($targetCount - $currentCount)
            ->pluck('id');

        if ($additional->isNotEmpty()) {
            $collection->products()->syncWithoutDetaching($additional->all());
        }
    }

    private function ensureBrand(string $name): Brand
    {
        $slug = Str::slug($name);
        $existingBrand = Brand::where('slug', $slug)->first();
        
        if ($existingBrand) {
            return $existingBrand;
        }
        
        // Use factory to create brand
        return Brand::factory()
            ->state([
                'slug' => $slug,
                'name' => $name,
                'is_enabled' => true,
            ])
            ->create();
    }

    private function ensureProductImage(Product $product, string $label, string $categoryName): void
    {
        if ($product->hasMedia('images')) {
            return;
        }

        try {
            $imagePath = $this->imageGenerator->generateProductImage($label, $categoryName);

            $product
                ->addMedia($imagePath)
                ->withCustomProperties(['source' => 'generated'])
                ->usingName($label.' Image')
                ->toMediaCollection('images');

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        } catch (\Throwable $exception) {
            $this->command?->warn('CollectionProductSeeder: failed to generate product image for '.$product->slug.': '.$exception->getMessage());
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en,ru,de')))
            ->map(fn ($locale) => trim($locale))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
