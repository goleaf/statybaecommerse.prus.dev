<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use App\Models\VariantAttributeValue;
use App\Models\VariantInventory;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use App\Models\VariantStockHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EnhancedProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createEnhancedAttributes();
            $this->createEnhancedProductsWithVariants();
            $this->createVariantAttributeValues();
            $this->createEnhancedPricingRules();
            $this->createEnhancedInventories();
            $this->createPriceHistory();
            $this->createStockHistory();
            $this->createAnalytics();
        });
    }

    private function createEnhancedAttributes(): void
    {
        // Enhanced size attribute with more options
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

        // Enhanced size values for clothing
        $clothingSizes = [
            ['value' => 'XXS', 'display' => 'Double Extra Small', 'lt' => 'Dvigubai Mažas', 'sort_order' => 1],
            ['value' => 'XS', 'display' => 'Extra Small', 'lt' => 'Labai Mažas', 'sort_order' => 2],
            ['value' => 'S', 'display' => 'Small', 'lt' => 'Mažas', 'sort_order' => 3],
            ['value' => 'M', 'display' => 'Medium', 'lt' => 'Vidutinis', 'sort_order' => 4],
            ['value' => 'L', 'display' => 'Large', 'lt' => 'Didelis', 'sort_order' => 5],
            ['value' => 'XL', 'display' => 'Extra Large', 'lt' => 'Labai Didelis', 'sort_order' => 6],
            ['value' => 'XXL', 'display' => 'Double Extra Large', 'lt' => 'Dvigubai Didelis', 'sort_order' => 7],
            ['value' => 'XXXL', 'display' => 'Triple Extra Large', 'lt' => 'Trigubai Didelis', 'sort_order' => 8],
        ];

        foreach ($clothingSizes as $size) {
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

        // Shoe sizes
        $shoeSizes = [
            ['value' => '35', 'display' => '35 EU', 'lt' => '35 EU', 'sort_order' => 1],
            ['value' => '36', 'display' => '36 EU', 'lt' => '36 EU', 'sort_order' => 2],
            ['value' => '37', 'display' => '37 EU', 'lt' => '37 EU', 'sort_order' => 3],
            ['value' => '38', 'display' => '38 EU', 'lt' => '38 EU', 'sort_order' => 4],
            ['value' => '39', 'display' => '39 EU', 'lt' => '39 EU', 'sort_order' => 5],
            ['value' => '40', 'display' => '40 EU', 'lt' => '40 EU', 'sort_order' => 6],
            ['value' => '41', 'display' => '41 EU', 'lt' => '41 EU', 'sort_order' => 7],
            ['value' => '42', 'display' => '42 EU', 'lt' => '42 EU', 'sort_order' => 8],
            ['value' => '43', 'display' => '43 EU', 'lt' => '43 EU', 'sort_order' => 9],
            ['value' => '44', 'display' => '44 EU', 'lt' => '44 EU', 'sort_order' => 10],
            ['value' => '45', 'display' => '45 EU', 'lt' => '45 EU', 'sort_order' => 11],
            ['value' => '46', 'display' => '46 EU', 'lt' => '46 EU', 'sort_order' => 12],
        ];

        foreach ($shoeSizes as $size) {
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

        // Enhanced color attribute
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

        $colors = [
            ['value' => 'Black', 'hex' => '#000000', 'lt' => 'Juoda', 'sort_order' => 1],
            ['value' => 'White', 'hex' => '#FFFFFF', 'lt' => 'Balta', 'sort_order' => 2],
            ['value' => 'Red', 'hex' => '#FF0000', 'lt' => 'Raudona', 'sort_order' => 3],
            ['value' => 'Blue', 'hex' => '#0000FF', 'lt' => 'Mėlyna', 'sort_order' => 4],
            ['value' => 'Green', 'hex' => '#008000', 'lt' => 'Žalia', 'sort_order' => 5],
            ['value' => 'Yellow', 'hex' => '#FFFF00', 'lt' => 'Geltona', 'sort_order' => 6],
            ['value' => 'Gray', 'hex' => '#808080', 'lt' => 'Pilka', 'sort_order' => 7],
            ['value' => 'Brown', 'hex' => '#A52A2A', 'lt' => 'Ruda', 'sort_order' => 8],
            ['value' => 'Pink', 'hex' => '#FFC0CB', 'lt' => 'Rožinė', 'sort_order' => 9],
            ['value' => 'Purple', 'hex' => '#800080', 'lt' => 'Violetinė', 'sort_order' => 10],
            ['value' => 'Orange', 'hex' => '#FFA500', 'lt' => 'Oranžinė', 'sort_order' => 11],
            ['value' => 'Navy', 'hex' => '#000080', 'lt' => 'Tamsiai mėlyna', 'sort_order' => 12],
        ];

        foreach ($colors as $color) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color['value'],
                ],
                [
                    'slug' => Str::slug($color['value']),
                    'display_value' => $color['value'],
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
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 3,
            ]
        );

        $materials = [
            ['value' => 'Cotton', 'lt' => 'Medvilnė', 'sort_order' => 1],
            ['value' => 'Polyester', 'lt' => 'Poliesteris', 'sort_order' => 2],
            ['value' => 'Wool', 'lt' => 'Vilna', 'sort_order' => 3],
            ['value' => 'Silk', 'lt' => 'Šilkas', 'sort_order' => 4],
            ['value' => 'Leather', 'lt' => 'Oda', 'sort_order' => 5],
            ['value' => 'Denim', 'lt' => 'Džinsas', 'sort_order' => 6],
            ['value' => 'Linen', 'lt' => 'Linas', 'sort_order' => 7],
            ['value' => 'Cashmere', 'lt' => 'Kašmyras', 'sort_order' => 8],
            ['value' => 'Synthetic', 'lt' => 'Sintetinis', 'sort_order' => 9],
        ];

        foreach ($materials as $material) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $materialAttribute->id,
                    'value' => $material['value'],
                ],
                [
                    'slug' => Str::slug($material['value']),
                    'display_value' => $material['value'],
                    'sort_order' => $material['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        // Style attribute
        $styleAttribute = Attribute::firstOrCreate(
            ['slug' => 'style'],
            [
                'name' => 'Style',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 4,
            ]
        );

        $styles = [
            ['value' => 'Casual', 'lt' => 'Kasualus', 'sort_order' => 1],
            ['value' => 'Formal', 'lt' => 'Formalus', 'sort_order' => 2],
            ['value' => 'Sport', 'lt' => 'Sportinis', 'sort_order' => 3],
            ['value' => 'Vintage', 'lt' => 'Vintage', 'sort_order' => 4],
            ['value' => 'Modern', 'lt' => 'Modernus', 'sort_order' => 5],
            ['value' => 'Classic', 'lt' => 'Klasikinis', 'sort_order' => 6],
        ];

        foreach ($styles as $style) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $styleAttribute->id,
                    'value' => $style['value'],
                ],
                [
                    'slug' => Str::slug($style['value']),
                    'display_value' => $style['value'],
                    'sort_order' => $style['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }
    }

    private function createEnhancedProductsWithVariants(): void
    {
        // Create multiple brands
        $brands = [
            ['name' => 'Fashion Brand', 'slug' => 'fashion-brand', 'description' => 'Premium fashion brand'],
            ['name' => 'Sport Brand', 'slug' => 'sport-brand', 'description' => 'Professional sports equipment'],
            ['name' => 'Luxury Brand', 'slug' => 'luxury-brand', 'description' => 'High-end luxury products'],
            ['name' => 'Casual Brand', 'slug' => 'casual-brand', 'description' => 'Everyday casual wear'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(
                ['slug' => $brandData['slug']],
                $brandData + ['is_enabled' => true]
            );
        }

        // Create multiple categories
        $categories = [
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'All types of clothing'],
            ['name' => 'Shoes', 'slug' => 'shoes', 'description' => 'Footwear for all occasions'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Fashion accessories'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports and athletic wear'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData + ['is_enabled' => true, 'is_visible' => true]
            );
        }

        // Enhanced products with comprehensive variants
        $products = [
            [
                'name' => 'Premium Cotton T-Shirt',
                'name_lt' => 'Premium Medvilnės Marškinėliai',
                'description' => 'High-quality cotton t-shirt with modern design and perfect fit',
                'description_lt' => 'Aukštos kokybės medvilnės marškinėliai su moderniu dizainu ir puikiu prigludimu',
                'base_price' => 29.99,
                'brand' => 'Fashion Brand',
                'category' => 'Clothing',
                'variants' => [
                    ['size' => 'XS', 'color' => 'Black', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 25, 'featured' => false],
                    ['size' => 'S', 'color' => 'Black', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 50, 'featured' => true],
                    ['size' => 'M', 'color' => 'Black', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 75, 'featured' => true],
                    ['size' => 'L', 'color' => 'Black', 'material' => 'Cotton', 'price_modifier' => 2.00, 'stock' => 60, 'featured' => false],
                    ['size' => 'XL', 'color' => 'Black', 'material' => 'Cotton', 'price_modifier' => 4.00, 'stock' => 40, 'featured' => false],
                    ['size' => 'XS', 'color' => 'White', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 30, 'featured' => false],
                    ['size' => 'S', 'color' => 'White', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 55, 'featured' => true],
                    ['size' => 'M', 'color' => 'White', 'material' => 'Cotton', 'price_modifier' => 0, 'stock' => 80, 'featured' => true],
                    ['size' => 'L', 'color' => 'White', 'material' => 'Cotton', 'price_modifier' => 2.00, 'stock' => 65, 'featured' => false],
                    ['size' => 'XL', 'color' => 'White', 'material' => 'Cotton', 'price_modifier' => 4.00, 'stock' => 45, 'featured' => false],
                ],
            ],
            [
                'name' => 'Designer Denim Jeans',
                'name_lt' => 'Dizainerių Džinsai',
                'description' => 'Premium denim jeans with perfect fit and modern styling',
                'description_lt' => 'Premium džinsai su puikiu prigludimu ir moderniu stiliumi',
                'base_price' => 89.99,
                'brand' => 'Fashion Brand',
                'category' => 'Clothing',
                'variants' => [
                    ['size' => '28', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 15, 'featured' => false],
                    ['size' => '30', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 25, 'featured' => true],
                    ['size' => '32', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 35, 'featured' => true],
                    ['size' => '34', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 5.00, 'stock' => 30, 'featured' => false],
                    ['size' => '36', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 10.00, 'stock' => 20, 'featured' => false],
                    ['size' => '38', 'color' => 'Blue', 'material' => 'Denim', 'price_modifier' => 15.00, 'stock' => 10, 'featured' => false],
                    ['size' => '28', 'color' => 'Black', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 20, 'featured' => true],
                    ['size' => '30', 'color' => 'Black', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 30, 'featured' => true],
                    ['size' => '32', 'color' => 'Black', 'material' => 'Denim', 'price_modifier' => 0, 'stock' => 40, 'featured' => true],
                    ['size' => '34', 'color' => 'Black', 'material' => 'Denim', 'price_modifier' => 5.00, 'stock' => 35, 'featured' => false],
                ],
            ],
            [
                'name' => 'Running Shoes',
                'name_lt' => 'Bėgimo Batai',
                'description' => 'Comfortable athletic shoes for running and training',
                'description_lt' => 'Patogūs sporto batai bėgimui ir treniruotėms',
                'base_price' => 129.99,
                'brand' => 'Sport Brand',
                'category' => 'Shoes',
                'variants' => [
                    ['size' => '36', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 20, 'featured' => false],
                    ['size' => '37', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 25, 'featured' => true],
                    ['size' => '38', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 30, 'featured' => true],
                    ['size' => '39', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 35, 'featured' => true],
                    ['size' => '40', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 40, 'featured' => true],
                    ['size' => '41', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 35, 'featured' => false],
                    ['size' => '42', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 30, 'featured' => false],
                    ['size' => '43', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 5.00, 'stock' => 25, 'featured' => false],
                    ['size' => '44', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 10.00, 'stock' => 20, 'featured' => false],
                    ['size' => '45', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 15.00, 'stock' => 15, 'featured' => false],
                    ['size' => '40', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 30, 'featured' => true],
                    ['size' => '41', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 25, 'featured' => true],
                    ['size' => '42', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 20, 'featured' => false],
                ],
            ],
            [
                'name' => 'Luxury Leather Jacket',
                'name_lt' => 'Prabangūs Odos Striukė',
                'description' => 'High-end leather jacket for all seasons',
                'description_lt' => 'Aukštos kokybės odos striukė visoms sezonoms',
                'base_price' => 299.99,
                'brand' => 'Luxury Brand',
                'category' => 'Clothing',
                'variants' => [
                    ['size' => 'S', 'color' => 'Black', 'material' => 'Leather', 'price_modifier' => 0, 'stock' => 10, 'featured' => true],
                    ['size' => 'M', 'color' => 'Black', 'material' => 'Leather', 'price_modifier' => 0, 'stock' => 15, 'featured' => true],
                    ['size' => 'L', 'color' => 'Black', 'material' => 'Leather', 'price_modifier' => 20.00, 'stock' => 12, 'featured' => false],
                    ['size' => 'XL', 'color' => 'Black', 'material' => 'Leather', 'price_modifier' => 40.00, 'stock' => 8, 'featured' => false],
                    ['size' => 'S', 'color' => 'Brown', 'material' => 'Leather', 'price_modifier' => 0, 'stock' => 8, 'featured' => true],
                    ['size' => 'M', 'color' => 'Brown', 'material' => 'Leather', 'price_modifier' => 0, 'stock' => 12, 'featured' => true],
                    ['size' => 'L', 'color' => 'Brown', 'material' => 'Leather', 'price_modifier' => 20.00, 'stock' => 10, 'featured' => false],
                ],
            ],
            [
                'name' => 'Casual Sneakers',
                'name_lt' => 'Kasualūs Kedsai',
                'description' => 'Comfortable casual sneakers for everyday wear',
                'description_lt' => 'Patogūs kasualūs kedsai kasdieniam nešiojimui',
                'base_price' => 79.99,
                'brand' => 'Casual Brand',
                'category' => 'Shoes',
                'variants' => [
                    ['size' => '36', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 40, 'featured' => true],
                    ['size' => '37', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 45, 'featured' => true],
                    ['size' => '38', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 50, 'featured' => true],
                    ['size' => '39', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 55, 'featured' => true],
                    ['size' => '40', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 60, 'featured' => true],
                    ['size' => '41', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 55, 'featured' => false],
                    ['size' => '42', 'color' => 'White', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 50, 'featured' => false],
                    ['size' => '39', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 35, 'featured' => true],
                    ['size' => '40', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 40, 'featured' => true],
                    ['size' => '41', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 35, 'featured' => false],
                    ['size' => '42', 'color' => 'Black', 'material' => 'Synthetic', 'price_modifier' => 0, 'stock' => 30, 'featured' => false],
                ],
            ],
        ];

        foreach ($products as $productData) {
            // Get or create brand
            $brand = Brand::where('slug', Str::slug($productData['brand']))->first();
            if (! $brand) {
                $brand = Brand::create([
                    'name' => $productData['brand'],
                    'slug' => Str::slug($productData['brand']),
                    'description' => 'Brand for '.$productData['name'],
                    'is_active' => true,
                ]);
            }

            // Get or create category
            $category = Category::where('slug', Str::slug($productData['category']))->first();
            if (! $category) {
                $category = Category::create([
                    'name' => $productData['category'],
                    'slug' => Str::slug($productData['category']),
                    'description' => 'Category for '.$productData['name'],
                    'is_active' => true,
                    'is_visible' => true,
                ]);
            }

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

            // Create variants with enhanced features
            foreach ($productData['variants'] as $index => $variantData) {
                $isOnSale = rand(0, 10) < 3; // 30% chance of being on sale
                $isNew = rand(0, 10) < 2; // 20% chance of being new
                $isBestseller = rand(0, 10) < 1; // 10% chance of being bestseller

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $productData['name'].' - '.$variantData['size'].' '.$variantData['color'],
                    'variant_name_lt' => $productData['name_lt'].' - '.$variantData['size'].' '.$this->getLocalizedValue('color', $variantData['color']),
                    'variant_name_en' => $productData['name'].' - '.$variantData['size'].' '.$variantData['color'],
                    'description_lt' => $productData['description_lt'].' Dydis: '.$variantData['size'].', Spalva: '.$this->getLocalizedValue('color', $variantData['color']),
                    'description_en' => $productData['description'].' Size: '.$variantData['size'].', Color: '.$variantData['color'],
                    'sku' => $product->sku.'-'.$variantData['size'].'-'.strtoupper(substr($variantData['color'], 0, 3)),
                    'price' => $productData['base_price'] + $variantData['price_modifier'],
                    'compare_price' => ($productData['base_price'] + $variantData['price_modifier']) * 1.2,
                    'cost_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.6,
                    'wholesale_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.8,
                    'member_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.9,
                    'promotional_price' => $isOnSale ? ($productData['base_price'] + $variantData['price_modifier']) * 0.8 : null,
                    'stock_quantity' => $variantData['stock'],
                    'reserved_quantity' => rand(0, min(5, $variantData['stock'])),
                    'available_quantity' => $variantData['stock'],
                    'sold_quantity' => rand(0, 50),
                    'weight' => 0.5,
                    'track_inventory' => true,
                    'is_default' => $index === 0,
                    'is_enabled' => true,
                    'is_on_sale' => $isOnSale,
                    'sale_start_date' => $isOnSale ? now()->subDays(rand(1, 30)) : null,
                    'sale_end_date' => $isOnSale ? now()->addDays(rand(1, 60)) : null,
                    'is_featured' => $variantData['featured'],
                    'is_new' => $isNew,
                    'is_bestseller' => $isBestseller,
                    'seo_title_lt' => $productData['name_lt'].' - '.$variantData['size'].' '.$this->getLocalizedValue('color', $variantData['color']),
                    'seo_title_en' => $productData['name'].' - '.$variantData['size'].' '.$variantData['color'],
                    'seo_description_lt' => $productData['description_lt'].' Kokybiškas produktas su geru prigludimu.',
                    'seo_description_en' => $productData['description'].' High-quality product with excellent fit.',
                    'views_count' => rand(10, 500),
                    'clicks_count' => rand(5, 100),
                    'conversion_rate' => rand(1, 15) / 100, // 0.01 to 0.15 (1% to 15%)
                    'attributes' => json_encode([
                        'size' => $variantData['size'],
                        'color' => $variantData['color'],
                        'material' => $variantData['material'],
                    ]),
                ]);

                // Update available quantity
                $variant->updateAvailableQuantity();

                // Create variant inventory
                VariantInventory::create([
                    'variant_id' => $variant->id,
                    'warehouse_code' => 'main',
                    'stock' => $variantData['stock'],
                    'reserved' => $variant->reserved_quantity,
                    'available' => $variant->available_quantity,
                    'reorder_point' => 10,
                    'reorder_quantity' => 50,
                ]);
            }
        }
    }

    private function createVariantAttributeValues(): void
    {
        $variants = ProductVariant::with('product')->get();
        $attributes = Attribute::with('values')->get();

        foreach ($variants as $variant) {
            $variantAttributes = json_decode($variant->attributes, true);

            if (! $variantAttributes) {
                continue;
            }

            foreach ($variantAttributes as $attributeName => $attributeValue) {
                $attribute = $attributes->where('slug', $attributeName)->first();
                if (! $attribute) {
                    continue;
                }

                $attributeValueRecord = $attribute->values->where('value', $attributeValue)->first();
                if (! $attributeValueRecord) {
                    continue;
                }

                VariantAttributeValue::create([
                    'variant_id' => $variant->id,
                    'attribute_id' => $attribute->id,
                    'attribute_name' => $attribute->name,
                    'attribute_value' => $attributeValue,
                    'attribute_value_display' => $attributeValueRecord->display_value,
                    'attribute_value_lt' => $this->getLocalizedValue($attributeName, $attributeValue),
                    'attribute_value_en' => $attributeValue,
                    'attribute_value_slug' => Str::slug($attributeValue),
                    'sort_order' => $attributeValueRecord->sort_order,
                    'is_filterable' => $attribute->is_filterable,
                    'is_searchable' => $attribute->is_searchable,
                ]);
            }
        }
    }

    private function createEnhancedPricingRules(): void
    {
        $products = Product::where('type', 'variable')->get();

        foreach ($products as $product) {
            // Size-based pricing rule for larger sizes
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

            // Color-based pricing rule for premium colors
            VariantPricingRule::create([
                'product_id' => $product->id,
                'rule_name' => 'Premium Color Pricing',
                'rule_type' => 'color_based',
                'conditions' => [
                    [
                        'attribute' => 'color',
                        'operator' => 'in',
                        'value' => ['Black', 'Navy'],
                    ],
                ],
                'pricing_modifiers' => [
                    [
                        'type' => 'fixed',
                        'value' => 5.00,
                    ],
                ],
                'is_active' => true,
                'priority' => 2,
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
                'priority' => 3,
            ]);
        }
    }

    private function createEnhancedInventories(): void
    {
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

            // Create regional warehouse inventory
            VariantInventory::create([
                'variant_id' => $variant->id,
                'warehouse_code' => 'regional',
                'stock' => rand(10, 40),
                'reserved' => rand(0, 5),
                'available' => rand(10, 40),
                'reorder_point' => 8,
                'reorder_quantity' => 30,
            ]);
        }
    }

    private function createPriceHistory(): void
    {
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Create some price history records
            $basePrice = $variant->cost_price;
            $currentPrice = $variant->price;

            // Historical price changes
            $oldPrice = $basePrice * 1.5; // Original higher price
            VariantPriceHistory::create([
                'variant_id' => $variant->id,
                'old_price' => $oldPrice,
                'new_price' => $currentPrice,
                'price_type' => 'regular',
                'change_reason' => 'Price adjustment',
                'changed_by' => null,
                'effective_from' => now()->subDays(rand(30, 90)),
                'effective_until' => null,
            ]);

            // Sale price history if on sale
            if ($variant->is_on_sale && $variant->promotional_price) {
                VariantPriceHistory::create([
                    'variant_id' => $variant->id,
                    'old_price' => $currentPrice,
                    'new_price' => $variant->promotional_price,
                    'price_type' => 'sale',
                    'change_reason' => 'Sale promotion',
                    'changed_by' => null,
                    'effective_from' => $variant->sale_start_date,
                    'effective_until' => $variant->sale_end_date,
                ]);
            }
        }
    }

    private function createStockHistory(): void
    {
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Create stock history records
            $initialStock = $variant->stock_quantity + rand(20, 100);
            $currentStock = $variant->stock_quantity;

            // Initial stock adjustment
            VariantStockHistory::create([
                'variant_id' => $variant->id,
                'old_quantity' => 0,
                'new_quantity' => $initialStock,
                'quantity_change' => $initialStock,
                'change_type' => 'restock',
                'change_reason' => 'Initial stock',
                'changed_by' => null,
                'reference_type' => null,
                'reference_id' => null,
            ]);

            // Some sales
            $soldQuantity = rand(5, 30);
            VariantStockHistory::create([
                'variant_id' => $variant->id,
                'old_quantity' => $initialStock,
                'new_quantity' => $initialStock - $soldQuantity,
                'quantity_change' => -$soldQuantity,
                'change_type' => 'sale',
                'change_reason' => 'Customer purchase',
                'changed_by' => null,
                'reference_type' => 'order',
                'reference_id' => rand(1000, 9999),
            ]);

            // Current stock adjustment
            if ($currentStock !== ($initialStock - $soldQuantity)) {
                VariantStockHistory::create([
                    'variant_id' => $variant->id,
                    'old_quantity' => $initialStock - $soldQuantity,
                    'new_quantity' => $currentStock,
                    'quantity_change' => $currentStock - ($initialStock - $soldQuantity),
                    'change_type' => 'adjustment',
                    'change_reason' => 'Stock adjustment',
                    'changed_by' => null,
                    'reference_type' => null,
                    'reference_id' => null,
                ]);
            }
        }
    }

    private function createAnalytics(): void
    {
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Create analytics for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->toDateString();

                $views = rand(0, 20);
                $clicks = $views > 0 ? rand(0, $views) : 0;
                $addToCart = $clicks > 0 ? rand(0, $clicks) : 0;
                $purchases = $addToCart > 0 ? rand(0, $addToCart) : 0;
                $revenue = $purchases * $variant->price;
                $conversionRate = $views > 0 ? ($purchases / $views) * 100 : 0;

                VariantAnalytics::create([
                    'variant_id' => $variant->id,
                    'date' => $date,
                    'views' => $views,
                    'clicks' => $clicks,
                    'add_to_cart' => $addToCart,
                    'purchases' => $purchases,
                    'revenue' => $revenue,
                    'conversion_rate' => $conversionRate,
                ]);
            }
        }
    }

    private function getLocalizedValue(string $attributeType, string $value): string
    {
        $localizations = [
            'color' => [
                'Black' => 'Juoda',
                'White' => 'Balta',
                'Red' => 'Raudona',
                'Blue' => 'Mėlyna',
                'Green' => 'Žalia',
                'Yellow' => 'Geltona',
                'Gray' => 'Pilka',
                'Brown' => 'Ruda',
                'Pink' => 'Rožinė',
                'Purple' => 'Violetinė',
                'Orange' => 'Oranžinė',
                'Navy' => 'Tamsiai mėlyna',
            ],
            'material' => [
                'Cotton' => 'Medvilnė',
                'Polyester' => 'Poliesteris',
                'Wool' => 'Vilna',
                'Silk' => 'Šilkas',
                'Leather' => 'Oda',
                'Denim' => 'Džinsas',
                'Linen' => 'Linas',
                'Cashmere' => 'Kašmyras',
                'Synthetic' => 'Sintetinis',
            ],
            'style' => [
                'Casual' => 'Kasualus',
                'Formal' => 'Formalus',
                'Sport' => 'Sportinis',
                'Vintage' => 'Vintage',
                'Modern' => 'Modernus',
                'Classic' => 'Klasikinis',
            ],
        ];

        return $localizations[$attributeType][$value] ?? $value;
    }
}
