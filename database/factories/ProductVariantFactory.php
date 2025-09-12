<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => fn () => \App\Models\Product::factory()->create()->id,
            'name' => Str::title($this->faker->words(2, true)),
            'sku' => strtoupper(Str::random(12)),
            'barcode' => $this->faker->boolean(40) ? strtoupper(Str::random(12)) : null,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'compare_price' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 500, 800) : null,
            'cost_price' => $this->faker->randomFloat(2, 5, 300),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->randomFloat(3, 0.1, 10.0),
            'track_inventory' => $this->faker->boolean(80),
            'is_default' => $this->faker->boolean(10),
            'is_enabled' => $this->faker->boolean(90),
            'attributes' => null,
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
