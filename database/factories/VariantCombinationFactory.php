<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\VariantCombination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\VariantCombination>
 */
class VariantCombinationFactory extends Factory
{
    protected $model = VariantCombination::class;

    public function definition(): array
    {
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
            ['material' => 'wood', 'finish' => 'natural'],
            ['material' => 'wood', 'finish' => 'stained'],
            ['material' => 'metal', 'finish' => 'painted'],
            ['material' => 'metal', 'finish' => 'galvanized'],
            ['power' => '18V', 'battery' => 'Li-ion'],
            ['power' => '24V', 'battery' => 'Li-ion'],
            ['power' => '36V', 'battery' => 'Li-ion'],
        ];

        return [
            'product_id' => Product::factory(),
            'attribute_combinations' => $this->faker->randomElement($combinations),
            'is_available' => $this->faker->boolean(85), // 85% chance of being available
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function withCombination(array $combination): static
    {
        return $this->state(fn (array $attributes) => [
            'attribute_combinations' => $combination,
        ]);
    }
}
