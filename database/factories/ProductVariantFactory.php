<?php declare(strict_types=1);

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
            'product_id' => fn() => \App\Models\Product::factory()->create()->id,
            'name' => Str::title($this->faker->words(2, true)),
            'sku' => strtoupper(Str::random(12)),
            'barcode' => $this->faker->boolean(40) ? strtoupper(Str::random(12)) : null,
            'allow_backorder' => $this->faker->boolean(10),
            'position' => $this->faker->numberBetween(0, 20),
            'metadata' => null,
            'status' => 'active',
        ];
    }
}
