<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use App\Models\VariantPricingRule;
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
        $sizeAttribute = Attribute::factory()
            ->state([
                'slug' => 'size',
                'name' => 'Size',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 1,
            ])
            ->create();

        $sizes = [
            ['value' => 'XS', 'display' => 'Extra Small', 'sort_order' => 1],
            ['value' => 'S', 'display' => 'Small', 'sort_order' => 2],
            ['value' => 'M', 'display' => 'Medium', 'sort_order' => 3],
            ['value' => 'L', 'display' => 'Large', 'sort_order' => 4],
            ['value' => 'XL', 'display' => 'Extra Large', 'sort_order' => 5],
            ['value' => 'XXL', 'display' => 'Double Extra Large', 'sort_order' => 6],
        ];

        foreach ($sizes as $size) {
            AttributeValue::factory()
                ->for($sizeAttribute)
                ->state([
                    'value' => $size['value'],
                    'slug' => Str::slug($size['value']),
                    'display_value' => $size['display'],
                    'sort_order' => $size['sort_order'],
                    'is_enabled' => true,
                ])
                ->create();
        }

        $colorAttribute = Attribute::factory()
            ->state([
                'slug' => 'color',
                'name' => 'Color',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 2,
            ])
            ->create();

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
            AttributeValue::factory()
                ->for($colorAttribute)
                ->state([
                    'value' => $color['value'],
                    'slug' => Str::slug($color['value']),
                    'hex_color' => $color['hex'],
                    'sort_order' => $color['sort_order'],
                    'is_enabled' => true,
                ])
                ->create();
        }

        $materialAttribute = Attribute::factory()
            ->state([
                'slug' => 'material',
                'name' => 'Material',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 3,
            ])
            ->create();

        $materials = [
            ['value' => 'Cotton', 'sort_order' => 1],
            ['value' => 'Polyester', 'sort_order' => 2],
            ['value' => 'Wool', 'sort_order' => 3],
            ['value' => 'Silk', 'sort_order' => 4],
            ['value' => 'Leather', 'sort_order' => 5],
            ['value' => 'Denim', 'sort_order' => 6],
        ];

        foreach ($materials as $material) {
            AttributeValue::factory()
                ->for($materialAttribute)
                ->state([
                    'value' => $material['value'],
                    'slug' => Str::slug($material['value']),
                    'sort_order' => $material['sort_order'],
                    'is_enabled' => true,
                ])
                ->create();
        }
    }

    private function createProductsWithVariants(): void
    {
        $brand = Brand::factory()
            ->state([
                'slug' => 'fashion-brand',
                'name' => 'Fashion Brand',
                'description' => 'Premium fashion brand',
                'is_enabled' => true,
            ])
            ->create();

        $category = Category::factory()
            ->state([
                'slug' => 'clothing',
                'name' => 'Clothing',
                'description' => 'Clothing category',
                'is_enabled' => true,
                'is_visible' => true,
            ])
            ->create();

        $products = [
            [
                'name' => 'Premium T-Shirt',
                'description' => 'High-quality cotton t-shirt with modern design',
                'base_price' => 29.99,
                'variants' => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 50],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 75],
                    ['size' => 'L', 'price_modifier' => 2.0, 'stock' => 60],
                    ['size' => 'XL', 'price_modifier' => 4.0, 'stock' => 40],
                    ['size' => 'XXL', 'price_modifier' => 6.0, 'stock' => 25],
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
                    ['size' => '34', 'price_modifier' => 5.0, 'stock' => 40],
                    ['size' => '36', 'price_modifier' => 10.0, 'stock' => 25],
                    ['size' => '38', 'price_modifier' => 15.0, 'stock' => 15],
                ],
            ],
            [
                'name' => 'Luxury Jacket',
                'description' => 'High-end leather jacket for all seasons',
                'base_price' => 299.99,
                'variants' => [
                    ['size' => 'S', 'price_modifier' => 0, 'stock' => 20],
                    ['size' => 'M', 'price_modifier' => 0, 'stock' => 25],
                    ['size' => 'L', 'price_modifier' => 20.0, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 40.0, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 60.0, 'stock' => 10],
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
                    ['size' => '43', 'price_modifier' => 5.0, 'stock' => 45],
                    ['size' => '44', 'price_modifier' => 10.0, 'stock' => 40],
                    ['size' => '45', 'price_modifier' => 15.0, 'stock' => 30],
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
                    ['size' => 'L', 'price_modifier' => 10.0, 'stock' => 20],
                    ['size' => 'XL', 'price_modifier' => 20.0, 'stock' => 15],
                    ['size' => 'XXL', 'price_modifier' => 30.0, 'stock' => 10],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::factory()
                ->for($brand)
                ->hasAttached($category)
                ->state([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'short_description' => substr($productData['description'], 0, 100),
                    'price' => $productData['base_price'],
                    'compare_price' => $productData['base_price'] * 1.2,
                    'cost_price' => $productData['base_price'] * 0.6,
                    'manage_stock' => true,
                    'stock_quantity' => 0,
                    'type' => 'variable',
                    'is_visible' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ])
                ->create();

            foreach ($productData['variants'] as $index => $variantData) {
                $variant = ProductVariant::factory()
                    ->for($product)
                    ->state([
                        'name' => $productData['name'] . ' - ' . $variantData['size'],
                        'sku' => $product->sku . '-' . $variantData['size'],
                        'price' => $productData['base_price'] + $variantData['price_modifier'],
                        'compare_price' => ($productData['base_price'] + $variantData['price_modifier']) * 1.2,
                        'cost_price' => ($productData['base_price'] + $variantData['price_modifier']) * 0.6,
                        'stock_quantity' => $variantData['stock'],
                        'is_default' => $index === 0,
                        'track_inventory' => true,
                        'is_enabled' => true,
                        'attributes' => ['size' => $variantData['size']],
                    ])
                    ->create();

                VariantInventory::factory()
                    ->for($variant)
                    ->state([
                        'warehouse_code' => 'main',
                        'stock' => $variantData['stock'],
                        'reserved' => 0,
                        'available' => $variantData['stock'],
                        'reorder_point' => 10,
                        'reorder_quantity' => 50,
                    ])
                    ->create();
            }
        }
    }

    private function createPricingRules(): void
    {
        // Size-based pricing rule for larger sizes
        $products = Product::where('type', 'variable')->get();

        foreach ($products as $product) {
            VariantPricingRule::factory()
                ->for($product)
                ->state([
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
                ])
                ->create();

            // Quantity-based discount rule
            VariantPricingRule::factory()
                ->for($product)
                ->state([
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
                            'value' => -10,  // 10% discount
                        ],
                    ],
                    'is_active' => true,
                    'priority' => 2,
                ])
                ->create();
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
                    'warehouse_code' => 'secondary',
                    'stock' => fake()->numberBetween(5, 25),
                    'reserved' => 0,
                    'available' => fake()->numberBetween(5, 25),
                    'reorder_point' => 5,
                    'reorder_quantity' => 25,
                ])
                ->create();
        }
    }
}
