<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
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

            // Create sample products with attributes using factories
            $products = collect();
            for ($i = 1; $i <= 3; $i++) {
                $product = Product::factory()->create([
                    'name' => "Sample Product {$i}",
                    'is_visible' => true,
                ]);

                // Create sample attributes using factories
                $colorAttribute = Attribute::factory()->create([
                    'name' => 'color',
                    'slug' => 'color',
                    'type' => 'select',
                    'is_required' => true,
                    'sort_order' => 1,
                ]);

                $sizeAttribute = Attribute::factory()->create([
                    'name' => 'size',
                    'slug' => 'size',
                    'type' => 'select',
                    'is_required' => true,
                    'sort_order' => 2,
                ]);

                // Create attribute values using factories
                $colorValues = AttributeValue::factory()->count(3)->create([
                    'attribute_id' => $colorAttribute->id,
                ])->each(function ($value, $index) {
                    $colors = ['red', 'blue', 'green'];
                    $value->update(['value' => $colors[$index]]);
                });

                $sizeValues = AttributeValue::factory()->count(3)->create([
                    'attribute_id' => $sizeAttribute->id,
                ])->each(function ($value, $index) {
                    $sizes = ['small', 'medium', 'large'];
                    $value->update(['value' => $sizes[$index]]);
                });

                // Attach attributes to product
                $product->attributes()->attach([
                    $colorAttribute->id,
                    $sizeAttribute->id,
                ]);

                $products->push($product);
            }
        }

        foreach ($products as $product) {
            $this->command->info("Creating variant combinations for product: {$product->name}");

            // Create variant combinations using factory
            $combinations = [
                ['color' => 'red', 'size' => 'small'],
                ['color' => 'red', 'size' => 'medium'],
                ['color' => 'red', 'size' => 'large'],
                ['color' => 'blue', 'size' => 'small'],
                ['color' => 'blue', 'size' => 'medium'],
                ['color' => 'blue', 'size' => 'large'],
                ['color' => 'green', 'size' => 'small'],
                ['color' => 'green', 'size' => 'medium'],
                ['color' => 'green', 'size' => 'large'],
            ];

            foreach ($combinations as $index => $combination) {
                VariantCombination::factory()
                    ->forProduct($product)
                    ->withCombination($combination)
                    ->state([
                        'is_available' => $index % 3 !== 1, // Make some unavailable for variety
                    ])
                    ->create();
            }

            $this->command->info('Created '.count($combinations)." variant combinations for {$product->name}");
        }

        $this->command->info('VariantCombination seeding completed successfully!');
    }
}
