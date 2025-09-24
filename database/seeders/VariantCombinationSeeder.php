<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\VariantCombination;
use Illuminate\Database\Seeder;

class VariantCombinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get products that have attributes
        $products = Product::whereHas('attributes')->take(5)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products with attributes found. Creating sample products first...');

            // Create sample products with attributes
            $products = collect();
            for ($i = 1; $i <= 3; $i++) {
                $product = Product::factory()->create([
                    'name' => "Sample Product {$i}",
                    'is_enabled' => true,
                ]);

                // Create sample attributes for the product
                $product->attributes()->createMany([
                    [
                        'name' => 'color',
                        'display_name' => 'Color',
                        'type' => 'select',
                        'is_required' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'size',
                        'display_name' => 'Size',
                        'type' => 'select',
                        'is_required' => true,
                        'sort_order' => 2,
                    ],
                ]);

                // Create attribute values
                $colorAttribute = $product->attributes()->where('name', 'color')->first();
                $sizeAttribute = $product->attributes()->where('name', 'size')->first();

                if ($colorAttribute) {
                    $colorAttribute->values()->createMany([
                        ['value' => 'red', 'display_name' => 'Red'],
                        ['value' => 'blue', 'display_name' => 'Blue'],
                        ['value' => 'green', 'display_name' => 'Green'],
                    ]);
                }

                if ($sizeAttribute) {
                    $sizeAttribute->values()->createMany([
                        ['value' => 'small', 'display_name' => 'Small'],
                        ['value' => 'medium', 'display_name' => 'Medium'],
                        ['value' => 'large', 'display_name' => 'Large'],
                    ]);
                }

                $products->push($product);
            }
        }

        foreach ($products as $product) {
            $this->command->info("Creating variant combinations for product: {$product->name}");

            // Create sample variant combinations
            $combinations = [
                [
                    'attribute_combinations' => [
                        'color' => 'red',
                        'size' => 'small',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'red',
                        'size' => 'medium',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'red',
                        'size' => 'large',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'blue',
                        'size' => 'small',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'blue',
                        'size' => 'medium',
                    ],
                    'is_available' => false, // Some unavailable
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'blue',
                        'size' => 'large',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'green',
                        'size' => 'small',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'green',
                        'size' => 'medium',
                    ],
                    'is_available' => true,
                ],
                [
                    'attribute_combinations' => [
                        'color' => 'green',
                        'size' => 'large',
                    ],
                    'is_available' => false, // Some unavailable
                ],
            ];

            foreach ($combinations as $combinationData) {
                VariantCombination::create([
                    'product_id' => $product->id,
                    'attribute_combinations' => $combinationData['attribute_combinations'],
                    'is_available' => $combinationData['is_available'],
                ]);
            }

            $this->command->info('Created '.count($combinations)." variant combinations for {$product->name}");
        }

        $this->command->info('VariantCombination seeding completed successfully!');
    }
}
