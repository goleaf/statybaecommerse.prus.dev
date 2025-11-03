<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use App\Models\VariantPricingRule;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAttributes();
        $this->createProductsWithVariants();
        $this->createPricingRules();
    }

    private function createAttributes(): void
    {
        // Create size attribute using factory
        $sizeAttribute = $this->ensureAttribute([
            'slug'          => 'size',
            'name'          => 'Size',
            'type'          => 'select',
            'is_required'   => true,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_enabled'    => true,
            'sort_order'    => 1,
        ]);

        $sizes = [
            ['value' => 'XS', 'display' => 'Extra Small', 'sort_order' => 1],
            ['value' => 'S', 'display' => 'Small', 'sort_order' => 2],
            ['value' => 'M', 'display' => 'Medium', 'sort_order' => 3],
            ['value' => 'L', 'display' => 'Large', 'sort_order' => 4],
            ['value' => 'XL', 'display' => 'Extra Large', 'sort_order' => 5],
            ['value' => 'XXL', 'display' => 'Double Extra Large', 'sort_order' => 6],
        ];

        foreach ($sizes as $size) {
            $this->ensureAttributeValue($sizeAttribute, [
                'value'         => $size['value'],
                'slug'          => Str::slug($size['value']),
                'display_value' => $size['display'],
                'sort_order'    => $size['sort_order'],
                'is_enabled'    => true,
            ]);
        }

        $colorAttribute = $this->ensureAttribute([
            'slug'          => 'color',
            'name'          => 'Color',
            'type'          => 'select',
            'is_required'   => false,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_enabled'    => true,
            'sort_order'    => 2,
        ]);

        $colors = [
            ['value' => 'Black', 'hex' => '#000000', 'sort_order' => 1],
            ['value' => 'White', 'hex' => '#FFFFFF', 'sort_order' => 2],
            ['value' => 'Red', 'hex' => '#FF0000', 'sort_order' => 3],
            ['value' => 'Blue', 'hex' => '#0000FF', 'sort_order' => 4],
            ['value' => 'Green', 'hex' => '#008000', 'sort_order' => 5],
            ['value' => 'Yellow', 'hex' => '#FFFF00', 'sort_order' => 6],
            ['value' => 'Gray', 'hex' => '#808080', 'sort_order' => 7],
            ['value' => 'Brown', 'hex' => '#A52A2A', 'sort_order' => 8],
        ];

        foreach ($colors as $color) {
            $this->ensureAttributeValue($colorAttribute, [
                'value'      => $color['value'],
                'slug'       => Str::slug($color['value']),
                'hex_color'  => $color['hex'],
                'sort_order' => $color['sort_order'],
                'is_enabled' => true,
            ]);
        }

        $materialAttribute = $this->ensureAttribute([
            'slug'          => 'material',
            'name'          => 'Material',
            'type'          => 'select',
            'is_required'   => false,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_enabled'    => true,
            'sort_order'    => 3,
        ]);

        $materials = [
            ['value' => 'Cotton', 'sort_order' => 1],
            ['value' => 'Polyester', 'sort_order' => 2],
            ['value' => 'Wool', 'sort_order' => 3],
            ['value' => 'Silk', 'sort_order' => 4],
            ['value' => 'Leather', 'sort_order' => 5],
            ['value' => 'Denim', 'sort_order' => 6],
        ];

        foreach ($materials as $material) {
            $this->ensureAttributeValue($materialAttribute, [
                'value'      => $material['value'],
                'slug'       => Str::slug($material['value']),
                'sort_order' => $material['sort_order'],
                'is_enabled' => true,
            ]);
        }
    }

    private function createProductsWithVariants(): void
    {
        $sizeAttribute = Attribute::query()->where('slug', 'size')->with('values')->first();
        $colorAttribute = Attribute::query()->where('slug', 'color')->with('values')->first();

        // Use existing brand instead of creating a new one
        $brand = Brand::query()->firstWhere('slug', 'fashion-brand');
        if (! $brand) {
            $existingBrands = Brand::query()->enabled()->get();
            if ($existingBrands->isNotEmpty()) {
                $brand = $existingBrands->first();
            } else {
                // Only create if no brands exist at all
                $brand = Brand::factory()
                    ->state([
                        'slug'        => 'fashion-brand',
                        'name'        => 'Fashion Brand',
                        'description' => 'Premium fashion brand',
                        'is_enabled'  => true,
                    ])
                    ->create();
            }
        }

        $category = $this->ensureCategory([
            'slug'        => 'clothing',
            'name'        => 'Clothing',
            'description' => 'Clothing category',
            'is_enabled'  => true,
            'is_visible'  => true,
        ]);

        $products = [
            [
                'name'        => 'Premium T-Shirt',
                'description' => 'High-quality cotton t-shirt with modern design',
                'base_price'  => 29.99,
                'variants'    => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 75],
                    ['size' => 'L', 'price_modifier' => 2.0, 'stock' => 60],
                    ['size' => 'XL', 'price_modifier' => 4.0, 'stock' => 40],
                    ['size' => 'XXL', 'price_modifier' => 6.0, 'stock' => 25],
                ],
            ],
            [
                'name'        => 'Designer Jeans',
                'description' => 'Premium denim jeans with perfect fit',
                'base_price'  => 89.99,
                'variants'    => [
                    ['size' => '28', 'price_modifier' => 0, 'stock' => 30],
                    ['size' => '30', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => '32', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '34', 'price_modifier' => 5.0, 'stock' => 40],
                    ['size' => '36', 'price_modifier' => 10.0, 'stock' => 25],
                    ['size' => '38', 'price_modifier' => 15.0, 'stock' => 15],
                ],
            ],
            [
                'name'        => 'Luxury Jacket',
                'description' => 'High-end leather jacket for all seasons',
                'base_price'  => 299.99,
                'variants'    => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 25],
                    ['size' => 'L', 'price_modifier' => 20.0, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 40.0, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 60.0, 'stock' => 10],
                ],
            ],
            [
                'name'        => 'Sports Shoes',
                'description' => 'Comfortable athletic shoes for running and training',
                'base_price'  => 129.99,
                'variants'    => [
                    ['size' => '36', 'price_modifier' => 0, 'stock' => 40],
                    ['size' => '37', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => '38', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => '39', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '40', 'price_modifier' => 0, 'stock' => 60],
                    ['size' => '41', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '42', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => '43', 'price_modifier' => 5.0, 'stock' => 45],
                    ['size' => '44', 'price_modifier' => 10.0, 'stock' => 40],
                    ['size' => '45', 'price_modifier' => 15.0, 'stock' => 30],
                ],
            ],
            [
                'name'        => 'Elegant Dress',
                'description' => 'Beautiful evening dress for special occasions',
                'base_price'  => 199.99,
                'variants'    => [
                    ['size' => 'XS', 'price_modifier' => 0, 'stock' => 15],
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 25],
                    ['size' => 'L', 'price_modifier' => 10.0, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 20.0, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 30.0, 'stock' => 10],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = $this->ensureProduct($brand, $category, $productData);

            $this->syncProductTranslations($product, $productData);

            foreach ($productData['variants'] as $index => $variantData) {
                $variant = $this->ensureVariant($product, $productData, $variantData, $index);

                $this->syncVariantTranslations($variant, $variantData, $productData);

                $matrix = [];

                if ($sizeAttribute) {
                    $sizeValue = $sizeAttribute->values->firstWhere(fn (AttributeValue $value) => strcasecmp($value->value, $variantData['size']) === 0)
                        ?? $sizeAttribute->values->firstWhere(fn (AttributeValue $value) => strcasecmp($value->display_value ?? '', $variantData['size']) === 0);

                    if ($sizeValue) {
                        $matrix['attribute_' . $sizeAttribute->getKey()] = $sizeValue->getKey();
                        $product->attributes()->syncWithoutDetaching([
                            $sizeAttribute->getKey() => ['attribute_value_id' => $sizeValue->getKey()],
                        ]);
                    }
                }

                if ($colorAttribute && $colorAttribute->values->isNotEmpty()) {
                    $colorValue = $colorAttribute->values->random();
                    $matrix['attribute_' . $colorAttribute->getKey()] = $colorValue->getKey();
                    $product->attributes()->syncWithoutDetaching([
                        $colorAttribute->getKey() => ['attribute_value_id' => $colorValue->getKey()],
                    ]);
                }

                if (! empty($matrix)) {
                    $variant->forceFill(['variant_attribute_matrix' => $matrix])->save();
                    ProductVariantAttributeMatrixService::sync($variant->fresh(), $matrix);
                }

                VariantInventory::query()->updateOrCreate(
                    [
                        'variant_id'     => $variant->getKey(),
                        'warehouse_code' => 'main',
                    ],
                    [
                        'stock'            => $variantData['stock'],
                        'reserved'         => 0,
                        'available'        => $variantData['stock'],
                        'reorder_point'    => 10,
                        'reorder_quantity' => 50,
                    ],
                );
            }
        }
    }

    /**
     * Ensure attribute exists and stays synchronized.
     */
    private function ensureAttribute(array $state): Attribute
    {
        $attribute = Attribute::query()->firstWhere('slug', $state['slug']);

        if ($attribute === null) {
            return Attribute::factory()->state($state)->create();
        }

        $attribute->forceFill($state);
        $attribute->save();

        return $attribute->fresh();
    }

    /**
     * Ensure attribute value exists for given attribute.
     */
    private function ensureAttributeValue(Attribute $attribute, array $state): AttributeValue
    {
        $state['slug'] ??= Str::slug($state['value']);

        $value = $attribute->values()->firstWhere('slug', $state['slug']);

        if ($value === null) {
            return AttributeValue::factory()
                ->for($attribute)
                ->state($state)
                ->create();
        }

        $value->forceFill($state);
        $value->save();

        return $value->fresh();
    }

    /**
     * Ensure category exists and is kept in sync.
     */
    private function ensureCategory(array $state): Category
    {
        $category = Category::query()->firstWhere('slug', $state['slug']);

        if ($category === null) {
            return Category::factory()->state($state)->create();
        }

        $category->forceFill($state);
        $category->save();

        return $category->fresh();
    }

    /**
     * Ensure product exists for brand/category combination.
     */
    private function ensureProduct(Brand $brand, Category $category, array $productData): Product
    {
        $slug = Str::slug($productData['name']);
        $state = [
            'name'              => $productData['name'],
            'slug'              => $slug,
            'description'       => $productData['description'],
            'short_description' => substr($productData['description'], 0, 100),
            'price'             => $productData['base_price'],
            'compare_price'     => $productData['base_price'] * 1.2,
            'cost_price'        => $productData['base_price'] * 0.6,
            'manage_stock'      => true,
            'stock_quantity'    => 0,
            'type'              => 'variable',
            'is_visible'        => true,
            'is_featured'       => true,
            'published_at'      => now(),
        ];

        $product = Product::query()->where('slug', $slug)->first();

        if ($product === null) {
            $product = Product::factory()
                ->for($brand)
                ->hasAttached($category)
                ->state($state)
                ->create();
        } else {
            $product->forceFill($state);
            $product->save();
            $product->categories()->syncWithoutDetaching([$category->getKey()]);
        }

        return $product->fresh();
    }

    /**
     * Ensure product variant exists and is updated.
     */
    private function ensureVariant(Product $product, array $productData, array $variantData, int $index): ProductVariant
    {
        $name = $productData['name'] . ' - ' . $variantData['size'];
        $baseSku = $product->sku ?? Str::upper(Str::slug($productData['name'], '-'));
        $sku = $baseSku . '-' . Str::slug((string) $variantData['size'], '-');
        $state = [
            'name'            => $name,
            'sku'             => $sku,
            'price'           => $productData['base_price'] + $variantData['price_modifier'],
            'compare_price'   => ($productData['base_price'] + $variantData['price_modifier']) * 1.2,
            'cost_price'      => ($productData['base_price'] + $variantData['price_modifier']) * 0.6,
            'stock_quantity'  => $variantData['stock'],
            'is_default'      => $index === 0,
            'track_inventory' => true,
            'is_enabled'      => true,
            'attributes'      => ['size' => $variantData['size']],
        ];

        $variant = $product->variants()->where('sku', $sku)->first();

        if ($variant === null) {
            return ProductVariant::factory()
                ->for($product)
                ->state($state)
                ->create();
        }

        $variant->forceFill($state);
        $variant->save();

        return $variant->fresh();
    }

    private function createPricingRules(): void
    {
        // Size-based pricing rule for larger sizes
        $products = Product::where('type', 'variable')->with('variants')->get();

        foreach ($products as $product) {
            VariantPricingRule::query()->updateOrCreate(
                [
                    'product_id' => $product->getKey(),
                    'name'       => 'Large Size Premium',
                ],
                [
                    'type'               => 'percentage',
                    'value'              => 5,
                    'priority'           => 1,
                    'is_active'          => true,
                    'is_cumulative'      => false,
                    'product_variant_id' => optional(
                        $product->variants->firstWhere(fn (ProductVariant $variant): bool => (
                            $variant->attributes['size'] ?? null
                        ) === 'XL')
                    )->getKey(),
                    'min_quantity' => null,
                    'max_quantity' => null,
                ],
            );

            VariantPricingRule::query()->updateOrCreate(
                [
                    'product_id' => $product->getKey(),
                    'name'       => 'Bulk Discount',
                ],
                [
                    'type'          => 'percentage',
                    'value'         => -10,
                    'priority'      => 2,
                    'is_active'     => true,
                    'is_cumulative' => true,
                    'min_quantity'  => 11,
                    'max_quantity'  => null,
                ],
            );
        }
    }

    private function createInventories(): void
    {
        // Create additional warehouse inventories
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Create secondary warehouse inventory
            VariantInventory::factory()
                ->for($variant)
                ->state([
                    'warehouse_code'   => 'secondary',
                    'stock'            => fake()->numberBetween(5, 25),
                    'reserved'         => 0,
                    'available'        => fake()->numberBetween(5, 25),
                    'reorder_point'    => 5,
                    'reorder_quantity' => 25,
                ])
                ->create();
        }
    }

    private function syncProductTranslations(Product $product, array $productData): void
    {
        if (! method_exists($product, 'translations')) {
            return;
        }

        $locales = $this->supportedLocales();

        foreach ($locales as $locale) {
            $name = data_get($productData, "translations.{$locale}.name", $productData['name']);
            $description = data_get($productData, "translations.{$locale}.description", $productData['description']);
            $shortDescription = data_get($productData, "translations.{$locale}.short_description", substr($description, 0, 120));
            $seoTitle = data_get($productData, "translations.{$locale}.seo_title", $name);
            $seoDescription = data_get($productData, "translations.{$locale}.seo_description", $description);

            $product->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name'              => $name,
                    'slug'              => Str::slug($name . '-' . $locale),
                    'summary'           => $shortDescription,
                    'description'       => $description,
                    'short_description' => $shortDescription,
                    'seo_title'         => $seoTitle,
                    'seo_description'   => $seoDescription,
                ],
            );
        }
    }

    private function syncVariantTranslations(ProductVariant $variant, array $variantData, array $productData): void
    {
        if (! method_exists($variant, 'translations')) {
            return;
        }

        $locales = $this->supportedLocales();

        foreach ($locales as $locale) {
            $name = data_get($variantData, "translations.{$locale}.name", $variant->name);
            $description = data_get($variantData, "translations.{$locale}.description", $productData['description']);
            $seoTitle = data_get($variantData, "translations.{$locale}.seo_title", $name);
            $seoDescription = data_get($variantData, "translations.{$locale}.seo_description", $description);

            $variant->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name'            => $name,
                    'description'     => $description,
                    'seo_title'       => $seoTitle,
                    'seo_description' => $seoDescription,
                ],
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $locales = config('app.supported_locales', ['lt', 'en']);

        if (is_string($locales)) {
            $locales = explode(',', $locales);
        }

        return collect($locales)
            ->map(static fn ($locale): string => trim((string) $locale))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
