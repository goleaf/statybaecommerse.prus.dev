<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Database\Seeder;

final class ProductFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::limit(15)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        // Define feature templates for different product types
        $featureTemplates = [
            'electronics' => [
                'specification' => [
                    'weight' => ['Lightweight', 'Heavy', 'Ultra-light'],
                    'dimensions' => ['Compact', 'Large', 'Portable'],
                    'battery_life' => ['Long-lasting', 'Quick charge', 'Extended'],
                    'connectivity' => ['WiFi', 'Bluetooth', 'USB-C', 'Wireless'],
                    'screen_size' => ['Small', 'Medium', 'Large', 'Extra Large'],
                ],
                'benefit' => [
                    'energy_efficient' => ['Saves power', 'Eco-friendly', 'Low consumption'],
                    'user_friendly' => ['Easy to use', 'Intuitive', 'Beginner-friendly'],
                    'durable' => ['Long-lasting', 'Robust', 'Reliable'],
                ],
                'technical' => [
                    'processor' => ['Fast', 'Efficient', 'High-performance'],
                    'memory' => ['Large capacity', 'Fast access', 'Expandable'],
                    'storage' => ['High capacity', 'Fast transfer', 'Secure'],
                ],
                'performance' => [
                    'speed' => ['Fast', 'Ultra-fast', 'Lightning quick'],
                    'quality' => ['High quality', 'Premium', 'Professional'],
                    'efficiency' => ['Optimized', 'Streamlined', 'Enhanced'],
                ],
            ],
            'clothing' => [
                'specification' => [
                    'material' => ['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen'],
                    'size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                    'color' => ['Black', 'White', 'Blue', 'Red', 'Green', 'Multi-color'],
                    'fit' => ['Slim', 'Regular', 'Loose', 'Oversized'],
                ],
                'benefit' => [
                    'comfort' => ['Comfortable', 'Soft', 'Breathable'],
                    'style' => ['Fashionable', 'Trendy', 'Classic', 'Modern'],
                    'versatility' => ['Versatile', 'Multi-purpose', 'Flexible'],
                ],
                'technical' => [
                    'care_instructions' => ['Machine wash', 'Hand wash', 'Dry clean'],
                    'fabric_technology' => ['Moisture-wicking', 'Stretch', 'Anti-bacterial'],
                ],
            ],
            'home_garden' => [
                'specification' => [
                    'dimensions' => ['Compact', 'Standard', 'Large'],
                    'material' => ['Wood', 'Metal', 'Plastic', 'Glass', 'Ceramic'],
                    'capacity' => ['Small', 'Medium', 'Large', 'Extra Large'],
                ],
                'benefit' => [
                    'durability' => ['Long-lasting', 'Weather-resistant', 'Sturdy'],
                    'aesthetics' => ['Beautiful', 'Elegant', 'Modern', 'Traditional'],
                    'functionality' => ['Practical', 'Multi-functional', 'Space-saving'],
                ],
                'technical' => [
                    'installation' => ['Easy install', 'Professional required', 'DIY friendly'],
                    'maintenance' => ['Low maintenance', 'Easy clean', 'Self-cleaning'],
                ],
            ],
        ];

        foreach ($products as $product) {
            $this->createFeaturesForProduct($product, $featureTemplates);
        }
    }

    /**
     * Create features for a specific product
     */
    private function createFeaturesForProduct(Product $product, array $templates): void
    {
        // Determine product category based on name or random selection
        $categories = array_keys($templates);
        $category = $categories[array_rand($categories)];
        $categoryFeatures = $templates[$category];

        $featureCount = rand(8, 15); // Each product gets 8-15 features
        $createdFeatures = 0;

        foreach ($categoryFeatures as $featureType => $features) {
            if ($createdFeatures >= $featureCount) {
                break;
            }

            $typeFeatures = $features;
            $selectedFeatures = array_rand($typeFeatures, min(rand(2, 4), count($typeFeatures)));

            if (! is_array($selectedFeatures)) {
                $selectedFeatures = [$selectedFeatures];
            }

            foreach ($selectedFeatures as $featureKey) {
                if ($createdFeatures >= $featureCount) {
                    break;
                }

                $featureValues = $typeFeatures[$featureKey];
                $selectedValue = $featureValues[array_rand($featureValues)];

                ProductFeature::create([
                    'product_id' => $product->id,
                    'feature_type' => $featureType,
                    'feature_key' => $featureKey,
                    'feature_value' => $selectedValue,
                    'weight' => $this->generateWeight($featureType),
                ]);

                $createdFeatures++;
            }
        }

        // Add some generic features
        $this->addGenericFeatures($product, $featureCount - $createdFeatures);
    }

    /**
     * Add generic features to reach target count
     */
    private function addGenericFeatures(Product $product, int $remainingCount): void
    {
        $genericFeatures = [
            'warranty' => ['1 year', '2 years', '3 years', '5 years', 'Lifetime'],
            'shipping' => ['Free shipping', 'Express delivery', 'Standard delivery'],
            'availability' => ['In stock', 'Limited quantity', 'Pre-order'],
            'rating' => ['5 stars', 'Highly rated', 'Customer favorite'],
            'popularity' => ['Best seller', 'Trending', 'Popular choice'],
        ];

        $featureTypes = ['specification', 'benefit', 'feature', 'technical', 'performance'];

        for ($i = 0; $i < $remainingCount; $i++) {
            $featureKey = array_rand($genericFeatures);
            $featureValues = $genericFeatures[$featureKey];
            $selectedValue = $featureValues[array_rand($featureValues)];
            $featureType = $featureTypes[array_rand($featureTypes)];

            ProductFeature::create([
                'product_id' => $product->id,
                'feature_type' => $featureType,
                'feature_key' => $featureKey,
                'feature_value' => $selectedValue,
                'weight' => $this->generateWeight($featureType),
            ]);
        }
    }

    /**
     * Generate weight based on feature type
     */
    private function generateWeight(string $featureType): float
    {
        return match ($featureType) {
            'specification' => rand(80, 100) / 100, // 0.8 - 1.0
            'benefit' => rand(70, 95) / 100, // 0.7 - 0.95
            'technical' => rand(60, 90) / 100, // 0.6 - 0.9
            'performance' => rand(75, 100) / 100, // 0.75 - 1.0
            default => rand(50, 85) / 100, // 0.5 - 0.85
        };
    }
}
