<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ComprehensiveProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createAttributes();
            $this->createProductsWithVariants();
            $this->createVariantAttributeValues();
        });
    }

    private function createAttributes(): void
    {
        // Size attributes
        $sizeAttribute = Attribute::firstOrCreate(
            ['slug' => 'product-size'],
            [
                'name' => 'Product Size',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 1,
            ]
        );

        // Color attributes
        $colorAttribute = Attribute::firstOrCreate(
            ['slug' => 'product-color'],
            [
                'name' => 'Product Color',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 2,
            ]
        );

        // Material attributes
        $materialAttribute = Attribute::firstOrCreate(
            ['slug' => 'product-material'],
            [
                'name' => 'Product Material',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 3,
            ]
        );

        // Create size values
        $sizes = [
            ['value' => 'XS', 'display' => 'Extra Small', 'lt' => 'Labai Mažas', 'sort_order' => 1],
            ['value' => 'S', 'display' => 'Small', 'lt' => 'Mažas', 'sort_order' => 2],
            ['value' => 'M', 'display' => 'Medium', 'lt' => 'Vidutinis', 'sort_order' => 3],
            ['value' => 'L', 'display' => 'Large', 'lt' => 'Didelis', 'sort_order' => 4],
            ['value' => 'XL', 'display' => 'Extra Large', 'lt' => 'Labai Didelis', 'sort_order' => 5],
            ['value' => 'XXL', 'display' => 'Double Extra Large', 'lt' => 'Dvigubai Didelis', 'sort_order' => 6],
        ];

        foreach ($sizes as $size) {
            $attributeValue = AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $sizeAttribute->id,
                    'value' => $size['value'],
                ],
                [
                    'slug' => 'product-size-'.Str::slug($size['value']),
                    'display_value' => $size['display'],
                    'sort_order' => $size['sort_order'],
                    'is_enabled' => true,
                ]
            );

            // Create translations
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'lt'],
                ['value' => $size['lt']]
            );
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'en'],
                ['value' => $size['display']]
            );
        }

        // Create color values
        $colors = [
            ['value' => 'black', 'display' => 'Black', 'lt' => 'Juoda', 'sort_order' => 1],
            ['value' => 'white', 'display' => 'White', 'lt' => 'Balta', 'sort_order' => 2],
            ['value' => 'red', 'display' => 'Red', 'lt' => 'Raudona', 'sort_order' => 3],
            ['value' => 'blue', 'display' => 'Blue', 'lt' => 'Mėlyna', 'sort_order' => 4],
            ['value' => 'green', 'display' => 'Green', 'lt' => 'Žalia', 'sort_order' => 5],
            ['value' => 'brown', 'display' => 'Brown', 'lt' => 'Ruda', 'sort_order' => 6],
        ];

        foreach ($colors as $color) {
            $attributeValue = AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color['value'],
                ],
                [
                    'slug' => 'product-color-'.Str::slug($color['value']),
                    'display_value' => $color['display'],
                    'sort_order' => $color['sort_order'],
                    'is_enabled' => true,
                ]
            );

            // Create translations
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'lt'],
                ['value' => $color['lt']]
            );
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'en'],
                ['value' => $color['display']]
            );
        }

        // Create material values
        $materials = [
            ['value' => 'cotton', 'display' => 'Cotton', 'lt' => 'Medvilnė', 'sort_order' => 1],
            ['value' => 'polyester', 'display' => 'Polyester', 'lt' => 'Poliesteris', 'sort_order' => 2],
            ['value' => 'wool', 'display' => 'Wool', 'lt' => 'Vilna', 'sort_order' => 3],
            ['value' => 'leather', 'display' => 'Leather', 'lt' => 'Oda', 'sort_order' => 4],
            ['value' => 'denim', 'display' => 'Denim', 'lt' => 'Džinsas', 'sort_order' => 5],
        ];

        foreach ($materials as $material) {
            $attributeValue = AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $materialAttribute->id,
                    'value' => $material['value'],
                ],
                [
                    'slug' => 'product-material-'.Str::slug($material['value']),
                    'display_value' => $material['display'],
                    'sort_order' => $material['sort_order'],
                    'is_enabled' => true,
                ]
            );

            // Create translations
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'lt'],
                ['value' => $material['lt']]
            );
            $attributeValue->translations()->updateOrCreate(
                ['locale' => 'en'],
                ['value' => $material['display']]
            );
        }
    }

    private function createProductsWithVariants(): void
    {
        // Get or create brands
        $brands = [
            'Nike' => ['name' => 'Nike'],
            'Adidas' => ['name' => 'Adidas'],
            'Puma' => ['name' => 'Puma'],
        ];

        $brandModels = [];
        foreach ($brands as $slug => $brandData) {
            $brandModels[$slug] = Brand::firstOrCreate(
                ['slug' => $slug],
                array_merge($brandData, [
                    'slug' => $slug,
                    'description' => 'Premium '.$brandData['name'].' products',
                    'is_enabled' => true,
                ])
            );
        }

        // Get or create categories
        $categories = [
            'clothing' => ['name' => 'Clothing'],
            'shoes' => ['name' => 'Shoes'],
            'accessories' => ['name' => 'Accessories'],
        ];

        $categoryModels = [];
        foreach ($categories as $slug => $categoryData) {
            $categoryModels[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                array_merge($categoryData, [
                    'slug' => $slug,
                    'description' => 'High-quality '.$categoryData['name'],
                    'is_enabled' => true,
                    'is_visible' => true,
                ])
            );
        }

        // Product data with variants
        $products = [
            [
                'name' => 'Classic T-Shirt',
                'name_lt' => 'Klasikinis Marškinėlis',
                'name_en' => 'Classic T-Shirt',
                'description' => 'Comfortable and stylish classic t-shirt made from premium cotton.',
                'description_lt' => 'Patogus ir stilingas klasikinis marškinėlis iš aukštos kokybės medvilnės.',
                'description_en' => 'Comfortable and stylish classic t-shirt made from premium cotton.',
                'base_price' => 29.99,
                'brand' => 'Nike',
                'category' => 'clothing',
                'variants' => [
                    ['size' => 'S', 'color' => 'black', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => 'M', 'color' => 'black', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 75],
                    ['size' => 'L', 'color' => 'black', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 60],
                    ['size' => 'XL', 'color' => 'black', 'material' => 'cotton', 'price_modifier' => 5.00, 'stock' => 40],
                    ['size' => 'S', 'color' => 'white', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => 'M', 'color' => 'white', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 70],
                    ['size' => 'L', 'color' => 'white', 'material' => 'cotton', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => 'XL', 'color' => 'white', 'material' => 'cotton', 'price_modifier' => 5.00, 'stock' => 35],
                ],
            ],
            [
                'name' => 'Running Shoes',
                'name_lt' => 'Bėgimo Batai',
                'name_en' => 'Running Shoes',
                'description' => 'High-performance running shoes with advanced cushioning technology.',
                'description_lt' => 'Aukštos kokybės bėgimo batai su pažangia amortizacijos technologija.',
                'description_en' => 'High-performance running shoes with advanced cushioning technology.',
                'base_price' => 129.99,
                'brand' => 'Adidas',
                'category' => 'shoes',
                'variants' => [
                    ['size' => 'S', 'color' => 'black', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 30],
                    ['size' => 'M', 'color' => 'black', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => 'L', 'color' => 'black', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 40],
                    ['size' => 'XL', 'color' => 'black', 'material' => 'polyester', 'price_modifier' => 10.00, 'stock' => 25],
                    ['size' => 'S', 'color' => 'white', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 35],
                    ['size' => 'M', 'color' => 'white', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => 'L', 'color' => 'white', 'material' => 'polyester', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => 'XL', 'color' => 'white', 'material' => 'polyester', 'price_modifier' => 10.00, 'stock' => 30],
                ],
            ],
            [
                'name' => 'Leather Jacket',
                'name_lt' => 'Odinis Striukė',
                'name_en' => 'Leather Jacket',
                'description' => 'Premium leather jacket with classic design and modern fit.',
                'description_lt' => 'Aukštos kokybės odinis striukė su klasikiniu dizainu ir moderniu siluetu.',
                'description_en' => 'Premium leather jacket with classic design and modern fit.',
                'base_price' => 299.99,
                'brand' => 'Puma',
                'category' => 'clothing',
                'variants' => [
                    ['size' => 'S', 'color' => 'black', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 15],
                    ['size' => 'M', 'color' => 'black', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'L', 'color' => 'black', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 18],
                    ['size' => 'XL', 'color' => 'black', 'material' => 'leather', 'price_modifier' => 25.00, 'stock' => 12],
                    ['size' => 'S', 'color' => 'brown', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 10],
                    ['size' => 'M', 'color' => 'brown', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 15],
                    ['size' => 'L', 'color' => 'brown', 'material' => 'leather', 'price_modifier' => 0, 'stock' => 12],
                    ['size' => 'XL', 'color' => 'brown', 'material' => 'leather', 'price_modifier' => 25.00, 'stock' => 8],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $brand = $brandModels[$productData['brand']];
            $category = $categoryModels[$productData['category']];

            $product = Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'],
                'short_description' => substr($productData['description'], 0, 100),
                'sku' => 'PROD-'.strtoupper(Str::random(8)),
                'price' => $productData['base_price'],
                'compare_price' => $productData['base_price'] * 1.2,
                'cost_price' => $productData['base_price'] * 0.6,
                'manage_stock' => true,
                'stock_quantity' => 0,
                'weight' => 0.5,
                'is_visible' => true,
                'is_featured' => true,
                'published_at' => now(),
                'brand_id' => $brand->id,
                'status' => 'published',
                'type' => 'variable',
            ]);

            // Attach category
            $product->categories()->attach($category->id);

            // Create variants
            foreach ($productData['variants'] as $index => $variantData) {
                $isOnSale = rand(0, 10) < 3; // 30% chance of being on sale
                $isNew = rand(0, 10) < 2; // 20% chance of being new
                $isBestseller = rand(0, 10) < 1; // 10% chance of being bestseller

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $productData['name'].' - '.$variantData['size'].' '.$variantData['color'],
                    'variant_name_lt' => $productData['name_lt'].' - '.$variantData['size'].' '.$this->getLocalizedValue('color', $variantData['color']),
                    'variant_name_en' => $productData['name_en'].' - '.$variantData['size'].' '.ucfirst($variantData['color']),
                    'description_lt' => $productData['description_lt'].' Dydis: '.$variantData['size'].', Spalva: '.$this->getLocalizedValue('color', $variantData['color']),
                    'description_en' => $productData['description_en'].' Size: '.$variantData['size'].', Color: '.ucfirst($variantData['color']),
                    'sku' => $product->sku.'-'.strtoupper($variantData['size']).'-'.strtoupper($variantData['color']),
                    'price' => $productData['base_price'] + $variantData['price_modifier'],
                    'compare_price' => ($productData['base_price'] + $variantData['price_modifier']) * 1.2,
                    'cost_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.6,
                    'wholesale_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.8,
                    'member_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.9,
                    'stock_quantity' => $variantData['stock'],
                    'reserved_quantity' => 0,
                    'available_quantity' => $variantData['stock'],
                    'sold_quantity' => rand(0, 20),
                    'weight' => 0.5 + ($variantData['size'] === 'XL' ? 0.1 : 0),
                    'track_inventory' => true,
                    'is_default' => $index === 0,
                    'is_enabled' => true,
                    'is_on_sale' => $isOnSale,
                    'sale_start_date' => $isOnSale ? now()->subDays(rand(1, 30)) : null,
                    'sale_end_date' => $isOnSale ? now()->addDays(rand(1, 30)) : null,
                    'is_featured' => rand(0, 10) < 3,
                    'is_new' => $isNew,
                    'is_bestseller' => $isBestseller,
                    'seo_title_lt' => $productData['name_lt'].' - '.$variantData['size'].' '.$this->getLocalizedValue('color', $variantData['color']),
                    'seo_title_en' => $productData['name_en'].' - '.$variantData['size'].' '.ucfirst($variantData['color']),
                    'seo_description_lt' => $productData['description_lt'].' Aukštos kokybės produktas.',
                    'seo_description_en' => $productData['description_en'].' High-quality product.',
                    'views_count' => rand(10, 500),
                    'clicks_count' => rand(5, 100),
                    'conversion_rate' => rand(1, 15) / 100,
                ]);
            }
        }
    }

    private function createVariantAttributeValues(): void
    {
        $variants = ProductVariant::with('product')->get();

        foreach ($variants as $variant) {
            // Extract size, color, and material from variant name
            $nameParts = explode(' - ', $variant->name);
            if (count($nameParts) >= 2) {
                $sizeColor = explode(' ', $nameParts[1]);
                $size = $sizeColor[0] ?? 'M';
                $color = $sizeColor[1] ?? 'black';

                // Get attribute IDs
                $sizeAttribute = Attribute::where('slug', 'product-size')->first();
                $colorAttribute = Attribute::where('slug', 'product-color')->first();
                $materialAttribute = Attribute::where('slug', 'product-material')->first();

                if ($sizeAttribute) {
                    $sizeValue = AttributeValue::where('attribute_id', $sizeAttribute->id)
                        ->where('value', $size)
                        ->first();

                    if ($sizeValue) {
                        VariantAttributeValue::firstOrCreate([
                            'variant_id' => $variant->id,
                            'attribute_id' => $sizeAttribute->id,
                        ], [
                            'attribute_name' => 'size',
                            'attribute_value' => $size,
                            'attribute_value_display' => $sizeValue->display_value,
                            'attribute_value_slug' => $sizeValue->slug,
                            'sort_order' => $sizeValue->sort_order,
                            'is_filterable' => true,
                            'is_searchable' => false,
                        ]);
                    }
                }

                if ($colorAttribute) {
                    $colorValue = AttributeValue::where('attribute_id', $colorAttribute->id)
                        ->where('value', $color)
                        ->first();

                    if ($colorValue) {
                        VariantAttributeValue::firstOrCreate([
                            'variant_id' => $variant->id,
                            'attribute_id' => $colorAttribute->id,
                        ], [
                            'attribute_name' => 'color',
                            'attribute_value' => $color,
                            'attribute_value_display' => $colorValue->display_value,
                            'attribute_value_slug' => $colorValue->slug,
                            'sort_order' => $colorValue->sort_order,
                            'is_filterable' => true,
                            'is_searchable' => true,
                        ]);
                    }
                }

                // Add material based on product type
                if ($materialAttribute) {
                    $material = $this->getMaterialForProduct($variant->product->name);
                    $materialValue = AttributeValue::where('attribute_id', $materialAttribute->id)
                        ->where('value', $material)
                        ->first();

                    if ($materialValue) {
                        VariantAttributeValue::firstOrCreate([
                            'variant_id' => $variant->id,
                            'attribute_id' => $materialAttribute->id,
                        ], [
                            'attribute_name' => 'material',
                            'attribute_value' => $material,
                            'attribute_value_display' => $materialValue->display_value,
                            'attribute_value_slug' => $materialValue->slug,
                            'sort_order' => $materialValue->sort_order,
                            'is_filterable' => true,
                            'is_searchable' => true,
                        ]);
                    }
                }
            }
        }
    }

    private function getLocalizedValue(string $type, string $value): string
    {
        $translations = [
            'color' => [
                'black' => 'Juoda',
                'white' => 'Balta',
                'red' => 'Raudona',
                'blue' => 'Mėlyna',
                'green' => 'Žalia',
                'brown' => 'Ruda',
            ],
        ];

        return $translations[$type][$value] ?? ucfirst($value);
    }

    private function getMaterialForProduct(string $productName): string
    {
        if (str_contains(strtolower($productName), 'shirt') || str_contains(strtolower($productName), 't-shirt')) {
            return 'cotton';
        } elseif (str_contains(strtolower($productName), 'shoes') || str_contains(strtolower($productName), 'running')) {
            return 'polyester';
        } elseif (str_contains(strtolower($productName), 'jacket') || str_contains(strtolower($productName), 'leather')) {
            return 'leather';
        }

        return 'cotton';
    }
}
