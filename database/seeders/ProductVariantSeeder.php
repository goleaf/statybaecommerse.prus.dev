<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\VariantPricingRule;
use App\Models\VariantInventory;
use App\Models\VariantImage;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createAttributes();
            $this->createProductsWithVariants();
            $this->createPricingRules();
            $this->createInventories();
        });
    }

    private function createAttributes(): void
    {
        // Size attribute
        $sizeAttribute = Attribute::firstOrCreate(
            ['slug' => 'size'],
            [
                'name' => 'Size',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 1,
            ]
        );

        // Create size values
        $sizes = [
            ['value' => 'XS', 'display' => 'Extra Small', 'sort_order' => 1],
            ['value' => 'S', 'display' => 'Small', 'sort_order' => 2],
            ['value' => 'M', 'display' => 'Medium', 'sort_order' => 3],
            ['value' => 'L', 'display' => 'Large', 'sort_order' => 4],
            ['value' => 'XL', 'display' => 'Extra Large', 'sort_order' => 5],
            ['value' => 'XXL', 'display' => 'Double Extra Large', 'sort_order' => 6],
        ];

        foreach ($sizes as $size) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $sizeAttribute->id,
                    'value' => $size['value'],
                ],
                [
                    'slug' => Str::slug($size['value']),
                    'display_value' => $size['display'],
                    'sort_order' => $size['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        // Color attribute
        $colorAttribute = Attribute::firstOrCreate(
            ['slug' => 'color'],
            [
                'name' => 'Color',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 2,
            ]
        );

        // Create color values
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
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color['value'],
                ],
                [
                    'slug' => Str::slug($color['value']),
                    'hex_color' => $color['hex'],
                    'sort_order' => $color['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        // Material attribute
        $materialAttribute = Attribute::firstOrCreate(
            ['slug' => 'material'],
            [
                'name' => 'Material',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 3,
            ]
        );

        // Create material values
        $materials = [
            ['value' => 'Cotton', 'sort_order' => 1],
            ['value' => 'Polyester', 'sort_order' => 2],
            ['value' => 'Wool', 'sort_order' => 3],
            ['value' => 'Silk', 'sort_order' => 4],
            ['value' => 'Leather', 'sort_order' => 5],
            ['value' => 'Denim', 'sort_order' => 6],
        ];

        foreach ($materials as $material) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $materialAttribute->id,
                    'value' => $material['value'],
                ],
                [
                    'slug' => Str::slug($material['value']),
                    'sort_order' => $material['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }
    }

    private function createProductsWithVariants(): void
    {
        // Get or create a brand
        $brand = Brand::firstOrCreate(
            ['name' => 'Fashion Brand'],
            [
                'slug' => 'fashion-brand',
                'description' => 'Premium fashion brand',
                'is_enabled' => true,
            ]
        );

        // Get or create a category
        $category = Category::firstOrCreate(
            ['name' => 'Clothing'],
            [
                'slug' => 'clothing',
                'description' => 'Clothing category',
                'is_enabled' => true,
                'is_visible' => true,
            ]
        );

        // Create products with variants
        $products = [
            [
                'name' => 'Premium T-Shirt',
                'description' => 'High-quality cotton t-shirt with modern design',
                'base_price' => 29.99,
                'variants' => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 75],
                    ['size' => 'L', 'price_modifier' => 2.00, 'stock' => 60],
                    ['size' => 'XL', 'price_modifier' => 4.00, 'stock' => 40],
                    ['size' => 'XXL', 'price_modifier' => 6.00, 'stock' => 25],
                ],
            ],
            [
                'name' => 'Designer Jeans',
                'description' => 'Premium denim jeans with perfect fit',
                'base_price' => 89.99,
                'variants' => [
                    ['size' => '28', 'price_modifier' => 0, 'stock' => 30],
                    ['size' => '30', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => '32', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '34', 'price_modifier' => 5.00, 'stock' => 40],
                    ['size' => '36', 'price_modifier' => 10.00, 'stock' => 25],
                    ['size' => '38', 'price_modifier' => 15.00, 'stock' => 15],
                ],
            ],
            [
                'name' => 'Luxury Jacket',
                'description' => 'High-end leather jacket for all seasons',
                'base_price' => 299.99,
                'variants' => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 25],
                    ['size' => 'L', 'price_modifier' => 20.00, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 40.00, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 60.00, 'stock' => 10],
                ],
            ],
            [
                'name' => 'Sports Shoes',
                'description' => 'Comfortable athletic shoes for running and training',
                'base_price' => 129.99,
                'variants' => [
                    ['size' => '36', 'price_modifier' => 0, 'stock' => 40],
                    ['size' => '37', 'price_modifier' => 0, 'stock' => 45],
                    ['size' => '38', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => '39', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '40', 'price_modifier' => 0, 'stock' => 60],
                    ['size' => '41', 'price_modifier' => 0, 'stock' => 55],
                    ['size' => '42', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => '43', 'price_modifier' => 5.00, 'stock' => 45],
                    ['size' => '44', 'price_modifier' => 10.00, 'stock' => 40],
                    ['size' => '45', 'price_modifier' => 15.00, 'stock' => 30],
                ],
            ],
            [
                'name' => 'Elegant Dress',
                'description' => 'Beautiful evening dress for special occasions',
                'base_price' => 199.99,
                'variants' => [
                    ['size' => 'XS', 'price_modifier' => 0, 'stock' => 15],
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 25],
                    ['size' => 'L', 'price_modifier' => 10.00, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 20.00, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 30.00, 'stock' => 10],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $nameEn = $productData['name'];
            $nameLt = match ($nameEn) {
                'Premium T-Shirt' => 'Premium marškinėliai',
                'Designer Jeans' => 'Dizainerio džinsai',
                'Luxury Jacket' => 'Prabangi striukė',
                'Sports Shoes' => 'Sportiniai batai',
                'Elegant Dress' => 'Elegantiška suknelė',
                default => $nameEn,
            };
            $slugEn = Str::slug($nameEn);
            $slugLt = Str::slug($nameLt);
            $sku = 'PROD-' . strtoupper(Str::slug($nameEn));

            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => ['lt' => $nameLt, 'en' => $nameEn],
                    'slug' => ['lt' => $slugLt, 'en' => $slugEn],
                    'description' => [
                        'lt' => 'Aukštos kokybės produktas: ' . $nameLt,
                        'en' => $productData['description'],
                    ],
                    'short_description' => [
                        'lt' => 'Trumpas aprašymas: ' . $nameLt,
                        'en' => substr($productData['description'], 0, 100),
                    ],
                    'price' => round($productData['base_price'], 2),
                    'compare_price' => round($productData['base_price'] * 1.2, 2),
                    'cost_price' => round($productData['base_price'] * 0.6, 2),
                    'manage_stock' => true,
                    'stock_quantity' => 0,
                    'weight' => 0.5,
                    'is_visible' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                    'brand_id' => $brand->id,
                    'status' => 'published',
                    'type' => 'variable',
                ]
            );

            // Attach category
            $product->categories()->attach($category->id);

            // Create variants
            foreach ($productData['variants'] as $index => $variantData) {
                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $product->sku . '-' . $variantData['size']],
                    [
                        'product_id' => $product->id,
                        'name' => $productData['name'] . ' - ' . $variantData['size'],
                        'sku' => $product->sku . '-' . $variantData['size'],
                        'price' => round($productData['base_price'] + $variantData['price_modifier'], 2),
                        'compare_price' => round(($productData['base_price'] + $variantData['price_modifier']) * 1.2, 2),
                        'cost_price' => round(($productData['base_price'] + $variantData['price_modifier']) * 0.6, 2),
                        'stock_quantity' => $variantData['stock'],
                        'weight' => 0.5,
                        'track_inventory' => true,
                        'is_default' => $index === 0,
                        'is_enabled' => true,
                        'attributes' => ['size' => $variantData['size']],
                    ]
                );

                // Note: Attributes are stored in JSON format in the attributes column
                // The relationship will be handled by the frontend components

                // Create variant inventory
                VariantInventory::updateOrCreate(
                    ['variant_id' => $variant->id, 'warehouse_code' => 'main'],
                    [
                        'variant_id' => $variant->id,
                        'warehouse_code' => 'main',
                        'stock' => $variantData['stock'],
                        'reserved' => 0,
                        'available' => $variantData['stock'],
                        'reorder_point' => 10,
                        'reorder_quantity' => 50,
                    ]
                );
            }
        }
    }

    private function createPricingRules(): void
    {
        // Size-based pricing rule for larger sizes
        $products = Product::where('type', 'variable')->get();

        foreach ($products as $product) {
            VariantPricingRule::create([
                'product_id' => $product->id,
                'rule_name' => 'Large Size Premium',
                'rule_type' => 'size_based',
                'conditions' => [
                    [
                        'attribute' => 'size',
                        'operator' => 'greater_than',
                        'value' => 'L',
                    ],
                ],
                'pricing_modifiers' => [
                    [
                        'type' => 'percentage',
                        'value' => 5,
                        'conditions' => [
                            [
                                'attribute' => 'size',
                                'operator' => 'equals',
                                'value' => 'XL',
                            ],
                        ],
                    ],
                    [
                        'type' => 'percentage',
                        'value' => 10,
                        'conditions' => [
                            [
                                'attribute' => 'size',
                                'operator' => 'equals',
                                'value' => 'XXL',
                            ],
                        ],
                    ],
                ],
                'is_active' => true,
                'priority' => 1,
            ]);

            // Quantity-based discount rule
            VariantPricingRule::create([
                'product_id' => $product->id,
                'rule_name' => 'Bulk Discount',
                'rule_type' => 'quantity_based',
                'conditions' => [
                    [
                        'attribute' => 'quantity',
                        'operator' => 'greater_than',
                        'value' => 10,
                    ],
                ],
                'pricing_modifiers' => [
                    [
                        'type' => 'percentage',
                        'value' => -10, // 10% discount
                    ],
                ],
                'is_active' => true,
                'priority' => 2,
            ]);
        }
    }

    private function createInventories(): void
    {
        // Create additional warehouse inventories
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Create secondary warehouse inventory
            VariantInventory::create([
                'variant_id' => $variant->id,
                'warehouse_code' => 'secondary',
                'stock' => rand(5, 25),
                'reserved' => 0,
                'available' => rand(5, 25),
                'reorder_point' => 5,
                'reorder_quantity' => 25,
            ]);
        }
    }
}
