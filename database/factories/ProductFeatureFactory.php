<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductFeature>
 */
final class ProductFeatureFactory extends Factory
{
    protected $model = ProductFeature::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'feature_type' => $this->faker->randomElement(['specification', 'benefit', 'feature', 'technical', 'performance']),
            'feature_key' => $this->faker->word(),
            'feature_value' => $this->faker->sentence(),
            'weight' => $this->faker->randomFloat(4, 0, 1),
        ];
    }

    /**
     * Create a specification feature
     */
    public function specification(): static
    {
        $specifications = [
            'weight' => ['Lightweight', 'Heavy', 'Ultra-light', 'Standard weight'],
            'dimensions' => ['Compact', 'Large', 'Portable', 'Standard size'],
            'material' => ['Plastic', 'Metal', 'Wood', 'Glass', 'Fabric'],
            'color' => ['Black', 'White', 'Blue', 'Red', 'Green', 'Multi-color'],
            'size' => ['Small', 'Medium', 'Large', 'Extra Large'],
        ];

        $key = $this->faker->randomElement(array_keys($specifications));
        $values = $specifications[$key];

        return $this->state(fn (array $attributes) => [
            'feature_type' => 'specification',
            'feature_key' => $key,
            'feature_value' => $this->faker->randomElement($values),
            'weight' => $this->faker->randomFloat(4, 0.8, 1.0),
        ]);
    }

    /**
     * Create a benefit feature
     */
    public function benefit(): static
    {
        $benefits = [
            'energy_efficient' => ['Saves power', 'Eco-friendly', 'Low consumption', 'Energy star rated'],
            'user_friendly' => ['Easy to use', 'Intuitive', 'Beginner-friendly', 'Simple operation'],
            'durable' => ['Long-lasting', 'Robust', 'Reliable', 'Built to last'],
            'comfortable' => ['Comfortable', 'Ergonomic', 'Soft', 'Cozy'],
            'versatile' => ['Versatile', 'Multi-purpose', 'Flexible', 'Adaptable'],
        ];

        $key = $this->faker->randomElement(array_keys($benefits));
        $values = $benefits[$key];

        return $this->state(fn (array $attributes) => [
            'feature_type' => 'benefit',
            'feature_key' => $key,
            'feature_value' => $this->faker->randomElement($values),
            'weight' => $this->faker->randomFloat(4, 0.7, 0.95),
        ]);
    }

    /**
     * Create a technical feature
     */
    public function technical(): static
    {
        $technical = [
            'processor' => ['Fast', 'Efficient', 'High-performance', 'Advanced'],
            'memory' => ['Large capacity', 'Fast access', 'Expandable', 'High-speed'],
            'storage' => ['High capacity', 'Fast transfer', 'Secure', 'Reliable'],
            'connectivity' => ['WiFi', 'Bluetooth', 'USB-C', 'Wireless', 'Ethernet'],
            'battery' => ['Long-lasting', 'Quick charge', 'Extended', 'High capacity'],
        ];

        $key = $this->faker->randomElement(array_keys($technical));
        $values = $technical[$key];

        return $this->state(fn (array $attributes) => [
            'feature_type' => 'technical',
            'feature_key' => $key,
            'feature_value' => $this->faker->randomElement($values),
            'weight' => $this->faker->randomFloat(4, 0.6, 0.9),
        ]);
    }

    /**
     * Create a performance feature
     */
    public function performance(): static
    {
        $performance = [
            'speed' => ['Fast', 'Ultra-fast', 'Lightning quick', 'High-speed'],
            'quality' => ['High quality', 'Premium', 'Professional', 'Superior'],
            'efficiency' => ['Optimized', 'Streamlined', 'Enhanced', 'Improved'],
            'accuracy' => ['Precise', 'Accurate', 'Reliable', 'Consistent'],
            'power' => ['Powerful', 'High-performance', 'Strong', 'Robust'],
        ];

        $key = $this->faker->randomElement(array_keys($performance));
        $values = $performance[$key];

        return $this->state(fn (array $attributes) => [
            'feature_type' => 'performance',
            'feature_key' => $key,
            'feature_value' => $this->faker->randomElement($values),
            'weight' => $this->faker->randomFloat(4, 0.75, 1.0),
        ]);
    }

    /**
     * Create a general feature
     */
    public function feature(): static
    {
        $features = [
            'warranty' => ['1 year', '2 years', '3 years', '5 years', 'Lifetime'],
            'shipping' => ['Free shipping', 'Express delivery', 'Standard delivery', 'Same day'],
            'availability' => ['In stock', 'Limited quantity', 'Pre-order', 'Backorder'],
            'rating' => ['5 stars', 'Highly rated', 'Customer favorite', 'Best seller'],
            'popularity' => ['Best seller', 'Trending', 'Popular choice', 'Top rated'],
        ];

        $key = $this->faker->randomElement(array_keys($features));
        $values = $features[$key];

        return $this->state(fn (array $attributes) => [
            'feature_type' => 'feature',
            'feature_key' => $key,
            'feature_value' => $this->faker->randomElement($values),
            'weight' => $this->faker->randomFloat(4, 0.5, 0.85),
        ]);
    }

    /**
     * Create a high-weight feature
     */
    public function highWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $this->faker->randomFloat(4, 0.8, 1.0),
        ]);
    }

    /**
     * Create a low-weight feature
     */
    public function lowWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $this->faker->randomFloat(4, 0.1, 0.4),
        ]);
    }

    /**
     * Create a feature with long description
     */
    public function longDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_value' => $this->faker->paragraph(3),
        ]);
    }

    /**
     * Create a feature with short description
     */
    public function shortDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_value' => $this->faker->word(),
        ]);
    }

    /**
     * Create a feature for a specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Create multiple features for the same product
     */
    public function forProductWithCount(Product $product, int $count = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ])->count($count);
    }
}
